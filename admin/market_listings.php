<?php
session_start();
include('db_connection.php');
include('config.php');
include('logging.php');
$db_conn = connectToDb();

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? "User";
$message = "";

// "I'm interested" - creates a transaction record the two parties confirm
// later from My Transactions.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'express_interest') {
    $listing_type = ($_POST['listing_type'] ?? '') === 'sell' ? 'sell' : 'buy';
    $listing_id = (int) ($_POST['listing_id'] ?? 0);
    $owner_id = (int) ($_POST['owner_id'] ?? 0);

    if ($owner_id && $owner_id != $user_id) {
        $stmt = $db_conn->prepare("
            SELECT Transaction_ID FROM transactions
            WHERE Listing_Type = :lt AND Listing_ID = :lid AND Initiator_User_ID = :uid
        ");
        $stmt->execute(['lt' => $listing_type, 'lid' => $listing_id, 'uid' => $user_id]);

        if ($stmt->fetch()) {
            $message = "You've already expressed interest in this listing - check My Transactions.";
        } else {
            $stmt = $db_conn->prepare("
                INSERT INTO transactions (Listing_Type, Listing_ID, Initiator_User_ID, Owner_User_ID)
                VALUES (:lt, :lid, :iuid, :ouid)
            ");
            $stmt->execute(['lt' => $listing_type, 'lid' => $listing_id, 'iuid' => $user_id, 'ouid' => $owner_id]);
            logActivity($db_conn, $user_id, 'express_interest', "{$listing_type} #{$listing_id}");
            $message = "Interest sent! Follow up from My Transactions on the main menu.";
        }
    }
}

// Optional filter by produce
$filter = trim($_GET['produce'] ?? '');

// Buyer requests (people looking to buy)
$sql_buy = "
    SELECT pb.Buy_ID, p.ProduceName, pb.Location, pb.Quantity_Value, pb.Quantity_Unit,
           pb.Price_Offered, pb.DateRequested, u.firstname, u.lastname, u.phone_number, pb.User_ID
    FROM produce_buy pb
    JOIN produce p ON pb.Produce_ID = p.ProduceID
    JOIN users u ON pb.User_ID = u.id
";
$params = [];
if ($filter !== '' && in_array($filter, $market_connect_produce_list, true)) {
    $sql_buy .= " WHERE p.ProduceName = :produce";
    $params['produce'] = $filter;
}
$sql_buy .= " ORDER BY pb.DateRequested DESC";
$stmt = $db_conn->prepare($sql_buy);
$stmt->execute($params);
$buy_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Seller listings (produce available for sale)
$sql_sell = "
    SELECT ps.Sell_ID, p.ProduceName, ps.Location, ps.Quantity_Value, ps.Quantity_Unit,
           ps.Price, u.firstname, u.lastname, u.phone_number, ps.User_ID
    FROM produce_sell ps
    JOIN produce p ON ps.Produce_ID = p.ProduceID
    JOIN users u ON ps.User_ID = u.id
";
if ($filter !== '' && in_array($filter, $market_connect_produce_list, true)) {
    $sql_sell .= " WHERE p.ProduceName = :produce";
}
$sql_sell .= " ORDER BY ps.Sell_ID DESC";
$stmt = $db_conn->prepare($sql_sell);
$stmt->execute($params);
$sell_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Market Listings - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <h2 class="text-success mb-3">📋 Market Listings</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-6">
            <label class="form-label">Filter by produce</label>
            <select name="produce" class="form-select" onchange="this.form.submit()">
                <option value="">-- All produce --</option>
                <?php foreach ($market_connect_produce_list as $crop): ?>
                    <option value="<?= htmlspecialchars($crop) ?>" <?= $filter === $crop ? 'selected' : '' ?>>
                        <?= htmlspecialchars($crop) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Buyers looking for produce -->
    <div class="card p-3 shadow mb-4">
        <h5>🛒 Buyers Looking for Produce (<?= count($buy_requests) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>Produce</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Price Offered</th>
                        <th>Buyer</th>
                        <th>Contact</th>
                        <th>Requested</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buy_requests as $b): ?>
                        <tr<?= $b['User_ID'] == $user_id ? ' class="table-success"' : '' ?>>
                            <td><?= htmlspecialchars($b['ProduceName']) ?></td>
                            <td><?= htmlspecialchars($b['Location']) ?></td>
                            <td><?= htmlspecialchars($b['Quantity_Value']) ?> <?= htmlspecialchars($b['Quantity_Unit']) ?></td>
                            <td>MWK <?= htmlspecialchars($b['Price_Offered']) ?></td>
                            <td><?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?><?= $b['User_ID'] == $user_id ? ' (You)' : '' ?></td>
                            <td><?= htmlspecialchars($b['phone_number']) ?></td>
                            <td><?= htmlspecialchars($b['DateRequested']) ?></td>
                            <td>
                                <?php if ($b['User_ID'] != $user_id): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="express_interest">
                                        <input type="hidden" name="listing_type" value="buy">
                                        <input type="hidden" name="listing_id" value="<?= (int) $b['Buy_ID'] ?>">
                                        <input type="hidden" name="owner_id" value="<?= (int) $b['User_ID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">I'm interested</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($buy_requests)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No buy requests yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sellers with produce available -->
    <div class="card p-3 shadow mb-4">
        <h5>🌾 Produce Available for Sale (<?= count($sell_listings) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>Produce</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Contact</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sell_listings as $s): ?>
                        <tr<?= $s['User_ID'] == $user_id ? ' class="table-success"' : '' ?>>
                            <td><?= htmlspecialchars($s['ProduceName']) ?></td>
                            <td><?= htmlspecialchars($s['Location']) ?></td>
                            <td><?= htmlspecialchars($s['Quantity_Value']) ?> <?= htmlspecialchars($s['Quantity_Unit']) ?></td>
                            <td>MWK <?= htmlspecialchars($s['Price']) ?></td>
                            <td><?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?><?= $s['User_ID'] == $user_id ? ' (You)' : '' ?></td>
                            <td><?= htmlspecialchars($s['phone_number']) ?></td>
                            <td>
                                <?php if ($s['User_ID'] != $user_id): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="express_interest">
                                        <input type="hidden" name="listing_type" value="sell">
                                        <input type="hidden" name="listing_id" value="<?= (int) $s['Sell_ID'] ?>">
                                        <input type="hidden" name="owner_id" value="<?= (int) $s['User_ID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">I'm interested</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($sell_listings)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No produce listed for sale yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small">
        These are all the listings currently in the system - not yet limited to "verified" buyers/sellers
        (that check is planned for the admin panel step).
    </p>
</div>

</body>
</html>
