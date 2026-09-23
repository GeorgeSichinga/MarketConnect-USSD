<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connection.php');
include('config.php');
include('alert_functions.php');
$db_conn = connectToDb();

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? "User";

// Placeholder price ranges per unit (MWK).
// TODO: replace with real ACE / Market Information System prices.
$price_ranges = [
    'KG'       => ['floor' => 300,   'ceiling' => 800],
    '50kg Bag' => ['floor' => 15000, 'ceiling' => 40000],
];

if (!isset($_SESSION['buy_step'])) $_SESSION['buy_step'] = 1;
if (!isset($_SESSION['buy_data']) || !is_array($_SESSION['buy_data'])) $_SESSION['buy_data'] = [];

$error = "";
$success = "";
$total_steps = 7;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If CANCEL button pressed
    if (isset($_POST['action']) && $_POST['action'] === "cancel") {
        session_destroy();
        echo "<script>
                alert('Thank you for using MarketConnect!');
                window.location.href='login.php';
              </script>";
        exit;
    }

    // If NEXT / DIAL button pressed
    if (isset($_POST['action']) && $_POST['action'] === "next") {

        $input = trim($_POST['value'] ?? '');
        $step = $_SESSION['buy_step'];

        switch ($step) {
            case 1: // Produce
                if ($input === '' || !in_array($input, $market_connect_produce_list, true)) {
                    $error = "Please choose a produce from the list.";
                } else {
                    $_SESSION['buy_data'] = [];
                    $_SESSION['buy_data']['produce_name'] = $input;
                    $_SESSION['buy_step'] = 2;
                }
                break;

            case 2: // Location
                if ($input === '') {
                    $error = "Enter location.";
                } else {
                    $_SESSION['buy_data']['location'] = $input;
                    $_SESSION['buy_step'] = 3;
                }
                break;

            case 3: // Quantity unit
                if ($input === "1") {
                    $_SESSION['buy_data']['quantity_unit'] = "KG";
                    $_SESSION['buy_step'] = 4;
                } elseif ($input === "2") {
                    $_SESSION['buy_data']['quantity_unit'] = "50kg Bag";
                    $_SESSION['buy_step'] = 4;
                } else {
                    $error = "Invalid option. Enter 1 for KG or 2 for 50kg Bag.";
                }
                break;

            case 4: // Quantity value
                if (!is_numeric($input) || $input <= 0) {
                    $error = "Enter a valid quantity greater than 0.";
                } else {
                    $_SESSION['buy_data']['quantity_value'] = $input;
                    $_SESSION['buy_step'] = 5;
                }
                break;

            case 5: // Price offered
                $unit = $_SESSION['buy_data']['quantity_unit'] ?? 'KG';
                $range = $price_ranges[$unit];
                if (!is_numeric($input) || $input < $range['floor'] || $input > $range['ceiling']) {
                    $error = "Price must be between MWK {$range['floor']} and MWK {$range['ceiling']} per {$unit}.";
                } else {
                    $_SESSION['buy_data']['price'] = $input;
                    $_SESSION['buy_step'] = 6;
                }
                break;

            case 6: // Confirm
                if ($input === "1") {
                    $p = $_SESSION['buy_data'];

                    try {
                        $stmt_check = $db_conn->prepare("SELECT ProduceID FROM produce WHERE ProduceName = :name");
                        $stmt_check->execute(['name' => $p['produce_name']]);
                        $prod = $stmt_check->fetch(PDO::FETCH_ASSOC);

                        if ($prod) {
                            $produce_id = $prod['ProduceID'];
                        } else {
                            $stmt_insert_prod = $db_conn->prepare("INSERT INTO produce (ProduceName) VALUES (:name)");
                            $stmt_insert_prod->execute(['name' => $p['produce_name']]);
                            $produce_id = $db_conn->lastInsertId();
                        }

                        $stmt_insert = $db_conn->prepare("
                            INSERT INTO produce_buy (User_ID, Produce_ID, Location, Quantity_Value, Quantity_Unit, Price_Offered, DateRequested)
                            VALUES (:uid, :pid, :loc, :qv, :qu, :price, :date)
                        ");
                        $stmt_insert->execute([
                            'uid'   => $user_id,
                            'pid'   => $produce_id,
                            'loc'   => $p['location'],
                            'qv'    => $p['quantity_value'],
                            'qu'    => $p['quantity_unit'],
                            'price' => $p['price'],
                            'date'  => date('Y-m-d H:i:s'),
                        ]);

                        // Scaffold: log + "send" a confirmation alert (see alert_functions.php).
                        try {
                            $msg = "Hi {$user_name}, your buy request for {$p['quantity_value']} {$p['quantity_unit']} of "
                                 . "{$p['produce_name']} at MWK {$p['price']} was posted.";
                            $alert_id = queueAlert($db_conn, $user_id, 'buy_request_posted', $msg);
                            sendAlert($db_conn, $alert_id);
                        } catch (PDOException $alertEx) {
                            // ignore for now - alerts are still a scaffold
                        }

                        $_SESSION['buy_step'] = 7;

                    } catch (PDOException $e) {
                        $error = "Database error: " . $e->getMessage();
                    }

                } elseif ($input === "2") {
                    $_SESSION['buy_step'] = 1;
                    $_SESSION['buy_data'] = [];
                    $success = "Request cancelled.";
                } else {
                    $error = "Invalid option. Enter 1 to Submit or 2 to Cancel.";
                }
                break;

            case 7: // After success
                if ($input === "1") {
                    $_SESSION['buy_step'] = 1;
                    $_SESSION['buy_data'] = [];
                } elseif ($input === "2") {
                    header("Location: select_role.php");
                    exit;
                } elseif ($input === "3") {
                    session_destroy();
                    echo "<script>
                            alert('Thank you for using MarketConnect!');
                            window.location.href='login.php';
                          </script>";
                    exit;
                } else {
                    $error = "Invalid option. Choose 1, 2, or 3.";
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buyer Dashboard - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 16px; }
        .card { max-width: 640px; margin: 12px auto; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-3">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <div class="card p-3 shadow-sm">
        <h3 class="text-success mb-2">🛒 Buyer Dashboard</h3>
        <p>Welcome, <strong><?= htmlspecialchars($user_name) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-info"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php $step = $_SESSION['buy_step']; ?>
            <p class="text-muted mb-1">Step <?= min($step, $total_steps) ?> of <?= $total_steps ?></p>

            <?php if ($step == 1): ?>
                <label>Select produce to buy</label>
                <select name="value" class="form-select" required>
                    <option value="" selected disabled>-- Choose produce --</option>
                    <?php foreach ($market_connect_produce_list as $crop): ?>
                        <option value="<?= htmlspecialchars($crop) ?>"><?= htmlspecialchars($crop) ?></option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ($step == 2): ?>
                <label>Enter location</label>
                <input type="text" name="value" class="form-control" placeholder="e.g. Kasungu" required>

            <?php elseif ($step == 3): ?>
                <label>Select quantity unit</label>
                <p class="mb-1">1. KG</p>
                <p>2. 50kg Bag</p>
                <input type="text" name="value" class="form-control" placeholder="Enter 1 or 2" required>

            <?php elseif ($step == 4): ?>
                <label>Enter quantity (in <?= htmlspecialchars($_SESSION['buy_data']['quantity_unit']) ?>)</label>
                <input type="number" name="value" class="form-control" min="1" required>

            <?php elseif ($step == 5):
                $unit = $_SESSION['buy_data']['quantity_unit'] ?? 'KG';
                $range = $price_ranges[$unit];
            ?>
                <label>Enter price offered per <?= htmlspecialchars($unit) ?></label>
                <p class="text-muted">Allowed: <?= $range['floor'] ?> - <?= $range['ceiling'] ?> MWK</p>
                <input type="number" name="value" class="form-control" min="<?= $range['floor'] ?>" max="<?= $range['ceiling'] ?>" required>

            <?php elseif ($step == 6): ?>
                <h5>Confirm request?</h5>
                <p class="mb-1">1. ✅ Post &amp; Submit</p>
                <p>2. ❌ Cancel</p>
                <input type="text" name="value" class="form-control" required>

            <?php elseif ($step == 7): ?>
                <h5>✅ Request Successful!</h5>
                <p class="mb-1">1. Add another request</p>
                <p class="mb-1">2. Return to main menu</p>
                <p>3. Exit</p>
                <input type="text" name="value" class="form-control" required>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-2">
                <button type="submit" name="action" value="next" class="btn btn-success flex-fill">Dial</button>
                <button type="submit" name="action" value="cancel" class="btn btn-danger flex-fill" formnovalidate>Cancel</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
