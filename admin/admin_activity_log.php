<?php
include('admin_auth.php');
include('db_connection.php');
$db_conn = connectToDb();

// Show the most recent 200 entries - keeps the page fast as the log grows.
$stmt = $db_conn->query("
    SELECT al.Log_ID, al.Action, al.Details, al.CreatedAt, u.firstname, u.lastname
    FROM activity_log al
    LEFT JOIN users u ON al.User_ID = u.id
    ORDER BY al.CreatedAt DESC
    LIMIT 200
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$action_labels = [
    'register'              => 'Registered',
    'login'                 => 'Logged in',
    'post_sell_listing'     => 'Posted sell listing',
    'post_buy_request'      => 'Posted buy request',
    'express_interest'      => 'Expressed interest',
    'confirm_transaction'   => 'Confirmed transaction',
    'admin_toggle_verify'   => 'Admin: toggled verification',
    'admin_set_usertype'    => 'Admin: changed user type',
    'admin_add_price'       => 'Admin: added price',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="admin_panel.php">&larr; Admin panel</a> &middot; <a href="select_role.php">Main menu</a></p>
    <h2 class="text-success mb-3">🧾 Activity Log</h2>
    <p class="text-muted small">Most recent 200 actions across the system (registrations, logins, listings, transactions, and admin changes).</p>

    <div class="card p-3 shadow mb-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['CreatedAt']) ?></td>
                            <td><?= htmlspecialchars(trim(($l['firstname'] ?? '') . ' ' . ($l['lastname'] ?? '')) ?: 'System') ?></td>
                            <td><?= htmlspecialchars($action_labels[$l['Action']] ?? $l['Action']) ?></td>
                            <td><?= htmlspecialchars($l['Details']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No activity recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
