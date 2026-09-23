<?php
include('admin_auth.php');
include('db_connection.php');
include('config.php');
include('logging.php');
$db_conn = connectToDb();

$message = "";
$admin_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produce_name = trim($_POST['produce'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $demand = trim($_POST['demand'] ?? '');
    $supply = trim($_POST['supply'] ?? '');

    if ($produce_name && $district && $price !== '' && $demand && $supply
        && in_array($produce_name, $market_connect_produce_list, true)
        && in_array($unit, ['KG', '50kg Bag'], true)) {

        $stmt_check = $db_conn->prepare("SELECT ProduceID FROM produce WHERE ProduceName = :name");
        $stmt_check->execute(['name' => $produce_name]);
        $prod = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($prod) {
            $stmt = $db_conn->prepare("
                INSERT INTO market_info (Produce_ID, District, Price, Price_Unit, Demand_Level, Supply_Level)
                VALUES (:pid, :district, :price, :unit, :demand, :supply)
            ");
            $stmt->execute([
                'pid'      => $prod['ProduceID'],
                'district' => $district,
                'price'    => $price,
                'unit'     => $unit,
                'demand'   => $demand,
                'supply'   => $supply,
            ]);
            logActivity($db_conn, $admin_id, 'admin_add_price', "{$produce_name} in {$district}: MWK {$price} per {$unit}");
            $message = "Price added for {$produce_name} in {$district}.";
        }
    } else {
        $message = "Please fill in all price fields with a valid produce and unit.";
    }
}

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
    <title>Manage Prices - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="admin_panel.php">&larr; Admin panel</a> &middot; <a href="select_role.php">Main menu</a></p>
    <h2 class="text-success mb-3">💰 Manage Prices</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card p-3 shadow mb-4">
        <h5>Add / Update a Price</h5>
        <form method="POST" class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Produce</label>
                <select name="produce" class="form-select" required>
                    <option value="" selected disabled>-- Choose produce --</option>
                    <?php foreach ($market_connect_produce_list as $crop): ?>
                        <option value="<?= htmlspecialchars($crop) ?>"><?= htmlspecialchars($crop) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">District</label>
                <input type="text" name="district" class="form-control" placeholder="e.g. Lilongwe" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Price (MWK)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Per</label>
                <select name="unit" class="form-select" required>
                    <option value="KG">KG</option>
                    <option value="50kg Bag">50kg Bag</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Demand Level</label>
                <select name="demand" class="form-select" required>
                    <option value="High">High</option>
                    <option value="Moderate">Moderate</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Supply Level</label>
                <select name="supply" class="form-select" required>
                    <option value="High">High</option>
                    <option value="Moderate">Moderate</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success w-100 mt-2">Save Price</button>
            </div>
        </form>
    </div>

    <div class="card p-3 shadow mb-4">
        <h5>Current Prices by District (<?= count($prices) ?>)</h5>
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
