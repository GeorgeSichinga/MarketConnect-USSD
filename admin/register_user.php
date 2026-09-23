<?php
session_start();
include('db_connection.php');
$db_conn = connectToDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = trim($_POST['phone']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
	$national_id = trim($_POST ['national_id']);
    $district = trim($_POST['district']);
    $TA = trim($_POST['TA']);
    $Village = trim($_POST['Village']);
    $nearestmarket = trim($_POST['nearestmarket']);
    $password = trim($_POST['password']);

    if ($phone_number === "" || $password === "") {
        echo "Please enter phone number and password.";
        exit;
    }

    try {
        $db_conn->beginTransaction();

        // Check if user already exists
        $stmtCheck = $db_conn->prepare("SELECT id FROM users WHERE phone_number = :phone");
        $stmtCheck->execute(['phone' => $phone_number]);
        if ($stmtCheck->rowCount() > 0) {
            echo "User already registered.";
            exit;
        }

        // Insert into users table
        $sql_user = "INSERT INTO market_connect.users (phone_number, firstname, lastname,national_id, district, TA, Village, nearestmarket)
                     VALUES (:phone, :firstname, :lastname, :national_id, :district, :TA, :Village, :nearestmarket)";
        $stmt_user = $db_conn->prepare($sql_user);
        $stmt_user->execute([
            'phone' => $phone_number,
            'firstname' => $firstname,
            'lastname' => $lastname,
			'national_id' => $national_id,
            'district' => $district,
            'TA' => $TA,
            'Village' => $Village,
            'nearestmarket' => $nearestmarket
        ]);

        $user_id = $db_conn->lastInsertId();

        // Insert plain password into authentication table
        $sql_auth = "INSERT INTO market_connect.authentication (user_id, password) VALUES (:user_id, :password)";
        $stmt_auth = $db_conn->prepare($sql_auth);
        $stmt_auth->execute([
            'user_id' => $user_id,
            'password' => $password
        ]);

        $db_conn->commit();
        echo "User registered successfully. You can now log in.";

    } catch (PDOException $e) {
        $db_conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
