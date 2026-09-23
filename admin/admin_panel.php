<?php
include('admin_auth.php');
include('db_connection.php');
$db_conn = connectToDb();

$user_name = $_SESSION['user_name'] ?? "Admin";

$total_users     = (int) $db_conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$verified_users  = (int) $db_conn->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();
$total_buys      = (int) $db_conn->query("SELECT COUNT(*) FROM produce_buy")->fetchColumn();
$total_sells     = (int) $db_conn->query("SELECT COUNT(*) FROM produce_sell")->fetchColumn();
$total_prices    = (int) $db_conn->query("SELECT COUNT(*) FROM market_info")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 720px;">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <h2 class="text-success mb-1">🛠️ Admin Panel</h2>
    <p>Signed in as <strong><?= htmlspecialchars($user_name) ?></strong> (Admin)</p>

    <div class="row text-center mb-4 g-2">
        <div class="col-4"><div class="card p-2 shadow-sm"><div class="fs-4"><?= $total_users ?></div><div class="text-muted small">Users</div></div></div>
        <div class="col-4"><div class="card p-2 shadow-sm"><div class="fs-4"><?= $verified_users ?></div><div class="text-muted small">Verified</div></div></div>
        <div class="col-4"><div class="card p-2 shadow-sm"><div class="fs-4"><?= $total_prices ?></div><div class="text-muted small">Prices posted</div></div></div>
        <div class="col-6"><div class="card p-2 shadow-sm"><div class="fs-4"><?= $total_sells ?></div><div class="text-muted small">Sell listings</div></div></div>
        <div class="col-6"><div class="card p-2 shadow-sm"><div class="fs-4"><?= $total_buys ?></div><div class="text-muted small">Buy requests</div></div></div>
    </div>

    <div class="list-group shadow-sm">
        <a href="admin_users.php" class="list-group-item list-group-item-action">
            👤 <strong>Manage Users</strong> — verify farmers/buyers, promote team members to Admin
        </a>
        <a href="admin_prices.php" class="list-group-item list-group-item-action">
            💰 <strong>Manage Prices</strong> — add/update market prices by district
        </a>
        <a href="admin_listings.php" class="list-group-item list-group-item-action">
            📋 <strong>View All Listings</strong> — every buy request and sell posting in the system
        </a>
        <a href="admin_activity_log.php" class="list-group-item list-group-item-action">
            🧾 <strong>Activity Log</strong> — logins, registrations, listings and admin actions
        </a>
    </div>
</div>

</body>
</html>
