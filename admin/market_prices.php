<?php
session_start();
include('db_connection.php');
include('config.php');
$db_conn = connectToDb();

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? "User";

$stmt = $db_conn->query("
    SELECT mi.MarketInfo_ID, p.ProduceName, mi.District, mi.Price, mi.Price_Unit, mi.Demand_Level, mi.Supply_Level, mi.DateUpdated
    FROM market_info mi
    JOIN produce p ON mi.Produce_ID = p.ProduceID
    ORDER BY mi.DateUpdated DESC
");
$prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Market Prices - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <h2 class="text-success mb-3">💰 Market Prices</h2>
    <p class="text-muted">Prices are entered and kept up to date by Market Connect administrators.</p>

    <div class="card p-3 shadow mb-4">
        <h5>Current Prices by District</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2">
                <thead>
                    <tr>
                        <th>Produce</th>
                        <th>District</th>
                        <th>Price (MWK)</th>
                        <th>Per</th>
                        <th>Demand</th>
                        <th>Supply</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prices as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['ProduceName']) ?></td>
                            <td><?= htmlspecialchars($p['District']) ?></td>
                            <td><?= htmlspecialchars($p['Price']) ?></td>
                            <td><?= htmlspecialchars($p['Price_Unit']) ?></td>
                            <td><?= htmlspecialchars($p['Demand_Level']) ?></td>
                            <td><?= htmlspecialchars($p['Supply_Level']) ?></td>
                            <td><?= htmlspecialchars($p['DateUpdated']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($prices)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No prices posted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
