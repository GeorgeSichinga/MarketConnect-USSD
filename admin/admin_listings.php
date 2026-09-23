<?php
include('admin_auth.php');
include('db_connection.php');
$db_conn = connectToDb();

$stmt = $db_conn->query("
    SELECT pb.Buy_ID, p.ProduceName, pb.Location, pb.Quantity_Value, pb.Quantity_Unit,
           pb.Price_Offered, pb.DateRequested, u.firstname, u.lastname, u.phone_number
    FROM produce_buy pb
    JOIN produce p ON pb.Produce_ID = p.ProduceID
    JOIN users u ON pb.User_ID = u.id
    ORDER BY pb.DateRequested DESC
");
$buy_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db_conn->query("
    SELECT ps.Sell_ID, p.ProduceName, ps.Location, ps.Quantity_Value, ps.Quantity_Unit,
           ps.Price, u.firstname, u.lastname, u.phone_number
    FROM produce_sell ps
    JOIN produce p ON ps.Produce_ID = p.ProduceID
    JOIN users u ON ps.User_ID = u.id
    ORDER BY ps.Sell_ID DESC
");
$sell_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Listings - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="admin_panel.php">&larr; Admin panel</a> &middot; <a href="select_role.php">Main menu</a></p>
    <h2 class="text-success mb-3">📋 All Listings</h2>

    <div class="card p-3 shadow mb-4">
        <h5>All Buy Requests (<?= count($buy_requests) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2">
                <thead>
                    <tr>
                        <th>Produce</th><th>Location</th><th>Quantity</th><th>Price Offered</th><th>Buyer</th><th>Contact</th><th>Requested</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buy_requests as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['ProduceName']) ?></td>
                            <td><?= htmlspecialchars($b['Location']) ?></td>
                            <td><?= htmlspecialchars($b['Quantity_Value']) ?> <?= htmlspecialchars($b['Quantity_Unit']) ?></td>
                            <td>MWK <?= htmlspecialchars($b['Price_Offered']) ?></td>
                            <td><?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?></td>
                            <td><?= htmlspecialchars($b['phone_number']) ?></td>
                            <td><?= htmlspecialchars($b['DateRequested']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($buy_requests)): ?>
                        <tr><td colspan="7" class="text-center text-muted">None yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3 shadow mb-4">
        <h5>All Sell Listings (<?= count($sell_listings) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2">
                <thead>
                    <tr>
                        <th>Produce</th><th>Location</th><th>Quantity</th><th>Price</th><th>Seller</th><th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sell_listings as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['ProduceName']) ?></td>
                            <td><?= htmlspecialchars($s['Location']) ?></td>
                            <td><?= htmlspecialchars($s['Quantity_Value']) ?> <?= htmlspecialchars($s['Quantity_Unit']) ?></td>
                            <td>MWK <?= htmlspecialchars($s['Price']) ?></td>
                            <td><?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?></td>
                            <td><?= htmlspecialchars($s['phone_number']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($sell_listings)): ?>
                        <tr><td colspan="6" class="text-center text-muted">None yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
