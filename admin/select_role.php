<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? "User";
$is_admin = ($_SESSION['usertype_id'] ?? null) == 3;
$error = "";

// Handle menu selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_input'])) {
    $role_input = strtolower(trim($_POST['role_input'])); // normalize input

    switch ($role_input) {
        case '1':
        case 'sell':
        case 'seller':
            $_SESSION['user_role'] = 'seller';
            header("Location: seller_dashboard.php");
            exit;

        case '2':
        case 'buy':
        case 'buyer':
            $_SESSION['user_role'] = 'buyer';
            header("Location: buyer_dashboard.php");
            exit;

        case '3':
        case 'listings':
            header("Location: market_listings.php");
            exit;

        case '4':
        case 'prices':
            header("Location: market_prices.php");
            exit;

        case '5':
        case 'weather':
            $_SESSION['user_role'] = 'weather';
            header("Location: weather_dashboard.php");
            exit;

        case '6':
        case 'transactions':
            header("Location: my_transactions.php");
            exit;

        case '7':
        case 'admin':
            if ($is_admin) {
                header("Location: admin_panel.php");
                exit;
            }
            $error = "❌ Invalid selection. Please enter a number from 1 to " . ($is_admin ? '7' : '6') . ".";
            break;

        default:
            $error = "❌ Invalid selection. Please enter a number from 1 to " . ($is_admin ? '7' : '6') . ".";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Main Menu - Market Connect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-6 mx-auto p-4 shadow rounded bg-white text-center">
        <h3 class="text-success mb-3">👋 Welcome, <?= htmlspecialchars($user_name) ?>!</h3>
        <p class="text-secondary mb-4">Please choose an option to continue:</p>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Menu form -->
        <form method="POST" action="">
            <div class="mb-3">
                <input type="text"
                       name="role_input"
                       class="form-control text-center"
                       placeholder="Enter 1-<?= $is_admin ? '7' : '6' ?>"
                       required>
            </div>

            <div class="text-start mb-3">
                <strong>Main Menu:</strong><br>
                1️⃣ Sell Produce<br>
                2️⃣ Buy Produce<br>
                3️⃣ View Market Listings (buyers &amp; sellers)<br>
                4️⃣ Market Prices<br>
                5️⃣ Weather Updates<br>
                6️⃣ My Transactions
                <?php if ($is_admin): ?>
                    <br>7️⃣ Admin Panel
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-success w-100">Submit</button>
        </form>
    </div>
</div>

</body>
</html>
