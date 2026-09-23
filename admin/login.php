<?php
session_start();
include('db_connection.php');
include('logging.php');
$db_conn = connectToDb();

$message = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if ($phone_number === "" || $password === "") {
        $message = "Please enter both phone number and password.";
    } else {
        // Fetch user info from users and authentication tables
        $stmt = $db_conn->prepare("
            SELECT u.id AS user_id, u.firstname, u.lastname, u.usertype_id, a.password
            FROM users u
            INNER JOIN authentication a ON u.id = a.user_id
            WHERE u.phone_number = :phone
            LIMIT 1
        ");
        $stmt->execute(['phone' => $phone_number]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user exists and password matches
        if ($user && $password === $user['password']) {
            // Set session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['firstname'];
            $_SESSION['usertype_id'] = $user['usertype_id'];

            logActivity($db_conn, $user['user_id'], 'login');

            // Redirect to role selection page instead of welcome.php
            header("Location: select_role.php");
            exit;
        } else {
            $message = "Invalid phone number or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Market Connect Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-5 mx-auto p-4 shadow rounded bg-white text-center">
        <h2 class="text-success mb-3">📱 Market Connect Portal</h2>
        <h6 class="text-secondary mb-4">Login to your account</h6>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="phone" class="form-control" placeholder="Enter Phone Number" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
    </div>
</div>

</body>
</html>
