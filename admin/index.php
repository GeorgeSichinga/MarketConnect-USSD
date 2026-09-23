<?php
session_start();
include('db_connection.php');
include('logging.php');
$db_conn = connectToDb();

$message = "";
$finished = false;

// Initialize registration session
if (!isset($_SESSION['registration'])) {
    $_SESSION['registration'] = [];
    $_SESSION['registration']['step'] = 0;
}

// Functions
function userExists($db_conn, $phone_number) {
    $stmt = $db_conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
    $stmt->execute(['phone_number' => $phone_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function registerUser($db_conn, $data) {
    $stmt = $db_conn->prepare("
        INSERT INTO users (firstname, lastname, national_id, phone_number, district, TA, village, nearestmarket)
        VALUES (:firstname, :lastname, :national_id, :phone_number, :district, :TA, :village, :nearestmarket)
    ");
    $stmt->execute($data);
    return $db_conn->lastInsertId();
}

function saveAuthentication($db_conn, $user_id, $password) {
    $stmt = $db_conn->prepare("
        INSERT INTO authentication (user_id, password)
        VALUES (:user_id, :password)
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'password' => $password
    ]);
}

// Steps order
$steps = ['firstname', 'lastname','national_id', 'phone', 'district', 'TA', 'village', 'nearestmarket', 'password'];
$currentStep = $_SESSION['registration']['step'];
$currentField = $steps[$currentStep];

// Field labels
$fieldLabels = [
    'firstname' => 'First Name',
    'lastname' => 'Last Name',
	'national_id'  => 'National ID',
    'phone' => 'Phone Number',
    'district' => 'District',
    'TA' => 'Traditional Authority (TA)',
    'village' => 'Village',
    'nearestmarket' => 'Nearest Market',
    'password' => 'Password'
];

// Handle input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['input']);

    // Save input in session
    $_SESSION['registration'][$currentField] = $input;

    // Move to next step
    $currentStep++;
    if ($currentStep >= count($steps)) {
        $finished = true;

        $data = [
            'firstname' => $_SESSION['registration']['firstname'],
            'lastname' => $_SESSION['registration']['lastname'],
			'national_id' => $_SESSION['registration']['national_id'],
            'phone_number' => $_SESSION['registration']['phone'],
            'district' => $_SESSION['registration']['district'],
            'TA' => $_SESSION['registration']['TA'],
            'village' => $_SESSION['registration']['village'],
            'nearestmarket' => $_SESSION['registration']['nearestmarket']
        ];
        $password = $_SESSION['registration']['password'];

        if (userExists($db_conn, $data['phone_number'])) {
            $message = "This phone number is already registered.";
        } else {
            $user_id = registerUser($db_conn, $data);
            saveAuthentication($db_conn, $user_id, $password);
            logActivity($db_conn, $user_id, 'register', "Phone {$data['phone_number']}");
            $message = "Registration complete! Here are your details:";
        }

        unset($_SESSION['registration']['step']);
    } else {
        $_SESSION['registration']['step'] = $currentStep;
        $currentField = $steps[$currentStep];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Market Connect Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-6 mx-auto p-4 shadow rounded bg-white">
        <h2 class="text-center text-success mb-3">📱 Welcome to Market Connect</h2>
        <h6 class="text-center text-secondary mb-4">Please fill in the details below to register</h6>

        <?php if (!$finished): ?>
            <p class="mb-3">Please insert your <?= strtolower($fieldLabels[$currentField]) ?> below:</p>
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="<?= $currentField === 'password' ? 'password' : 'text' ?>" 
                           name="input" 
                           class="form-control" 
                           placeholder="<?= $fieldLabels[$currentField] ?>" 
                           required>
                </div>
                <button type="submit" class="btn btn-success w-100">Next</button>
            </form>

        <?php else: ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($message) ?></p>
                <ul class="list-group list-group-flush text-start">
                    <?php foreach ($steps as $step): ?>
                        <li class="list-group-item"><strong><?= $fieldLabels[$step] ?>:</strong> <?= htmlspecialchars($_SESSION['registration'][$step]) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="mt-2">Redirecting to login...</p>
            </div>
            <?php
            unset($_SESSION['registration']);
            echo '<meta http-equiv="refresh" content="3;url=login.php">';
            ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
