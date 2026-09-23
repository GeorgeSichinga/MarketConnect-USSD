<?php
session_start();
include('db_connection.php');
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

// Handle a Yes/No confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm') {
    $tid = (int) ($_POST['transaction_id'] ?? 0);
    $value = ($_POST['value'] ?? '') === 'yes' ? 'yes' : 'no';

    $stmt = $db_conn->prepare("SELECT * FROM transactions WHERE Transaction_ID = :id");
    $stmt->execute(['id' => $tid]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($t && ($t['Owner_User_ID'] == $user_id || $t['Initiator_User_ID'] == $user_id)) {
        $column = ($t['Owner_User_ID'] == $user_id) ? 'Owner_Confirmed' : 'Initiator_Confirmed';
        $stmt = $db_conn->prepare("UPDATE transactions SET {$column} = :v WHERE Transaction_ID = :id");
        $stmt->execute(['v' => $value, 'id' => $tid]);

        // Recompute overall status from both confirmations
        $stmt = $db_conn->prepare("SELECT Initiator_Confirmed, Owner_Confirmed FROM transactions WHERE Transaction_ID = :id");
        $stmt->execute(['id' => $tid]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($c['Initiator_Confirmed'] === 'no' || $c['Owner_Confirmed'] === 'no') {
            $new_status = 'declined';
        } elseif ($c['Initiator_Confirmed'] === 'yes' && $c['Owner_Confirmed'] === 'yes') {
            $new_status = 'completed';
        } else {
            $new_status = 'pending';
        }
        $stmt = $db_conn->prepare("UPDATE transactions SET Status = :s WHERE Transaction_ID = :id");
        $stmt->execute(['s' => $new_status, 'id' => $tid]);

        logActivity($db_conn, $user_id, 'confirm_transaction', "Transaction #{$tid} -> {$value} (status: {$new_status})");
        $message = "Response recorded.";
    }
}

// Pull a transaction list for either "owner" (people interested in my listings)
// or "initiator" (listings I've shown interest in) and attach the listing +
// other-party details in PHP - simpler to follow than one big SQL join.
function getTransactionsForUser($db_conn, $role, $user_id) {
    $user_col = $role === 'owner' ? 'Owner_User_ID' : 'Initiator_User_ID';
    $other_col = $role === 'owner' ? 'Initiator_User_ID' : 'Owner_User_ID';

    $stmt = $db_conn->prepare("SELECT * FROM transactions WHERE {$user_col} = :uid ORDER BY CreatedAt DESC");
    $stmt->execute(['uid' => $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($rows as $t) {
        if ($t['Listing_Type'] === 'sell') {
            $stmt2 = $db_conn->prepare("
                SELECT p.ProduceName, ps.Location, ps.Quantity_Value, ps.Quantity_Unit, ps.Price
                FROM produce_sell ps JOIN produce p ON ps.Produce_ID = p.ProduceID
                WHERE ps.Sell_ID = :id
            ");
        } else {
            $stmt2 = $db_conn->prepare("
                SELECT p.ProduceName, pb.Location, pb.Quantity_Value, pb.Quantity_Unit, pb.Price_Offered AS Price
                FROM produce_buy pb JOIN produce p ON pb.Produce_ID = p.ProduceID
                WHERE pb.Buy_ID = :id
            ");
        }
        $stmt2->execute(['id' => $t['Listing_ID']]);
        $listing = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$listing) continue; // listing may have been removed since

        $stmt3 = $db_conn->prepare("SELECT firstname, lastname, phone_number FROM users WHERE id = :id");
        $stmt3->execute(['id' => $t[$other_col]]);
        $other = $stmt3->fetch(PDO::FETCH_ASSOC);

        $results[] = [
            'transaction_id'     => $t['Transaction_ID'],
            'listing_type'       => $t['Listing_Type'],
            'produce'            => $listing['ProduceName'],
            'location'           => $listing['Location'],
            'quantity'           => $listing['Quantity_Value'] . ' ' . $listing['Quantity_Unit'],
            'price'              => $listing['Price'],
            'other_name'         => trim($other['firstname'] . ' ' . $other['lastname']),
            'other_phone'        => $other['phone_number'] ?? '',
            'status'             => $t['Status'],
            'my_confirmation'    => $role === 'owner' ? $t['Owner_Confirmed'] : $t['Initiator_Confirmed'],
            'created_at'         => $t['CreatedAt'],
        ];
    }
    return $results;
}

$as_owner = getTransactionsForUser($db_conn, 'owner', $user_id);
$as_initiator = getTransactionsForUser($db_conn, 'initiator', $user_id);

function statusBadge($status) {
    $map = ['pending' => 'secondary', 'completed' => 'success', 'declined' => 'danger'];
    $class = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Transactions - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="select_role.php">&larr; Main menu</a></p>
    <h2 class="text-success mb-3">🤝 My Transactions</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- People interested in MY listings -->
    <div class="card p-3 shadow mb-4">
        <h5>Interest shown in my listings (<?= count($as_owner) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>Produce</th><th>Quantity</th><th>Price</th><th>Other party</th><th>Contact</th><th>Status</th><th>Confirm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($as_owner as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['produce']) ?> (<?= htmlspecialchars(ucfirst($t['listing_type'])) ?>)</td>
                            <td><?= htmlspecialchars($t['quantity']) ?></td>
                            <td>MWK <?= htmlspecialchars($t['price']) ?></td>
                            <td><?= htmlspecialchars($t['other_name']) ?></td>
                            <td><?= htmlspecialchars($t['other_phone']) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'pending' && $t['my_confirmation'] === 'pending'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="action" value="confirm">
                                        <input type="hidden" name="transaction_id" value="<?= (int) $t['transaction_id'] ?>">
                                        <button type="submit" name="value" value="yes" class="btn btn-sm btn-success">Sale completed</button>
                                        <button type="submit" name="value" value="no" class="btn btn-sm btn-outline-danger">Not completed</button>
                                    </form>
                                <?php elseif ($t['my_confirmation'] !== 'pending'): ?>
                                    <span class="text-muted small">You said <?= htmlspecialchars($t['my_confirmation']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($as_owner)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No one has expressed interest in your listings yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Listings I've shown interest in -->
    <div class="card p-3 shadow mb-4">
        <h5>My interest requests (<?= count($as_initiator) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>Produce</th><th>Quantity</th><th>Price</th><th>Other party</th><th>Contact</th><th>Status</th><th>Confirm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($as_initiator as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['produce']) ?> (<?= htmlspecialchars(ucfirst($t['listing_type'])) ?>)</td>
                            <td><?= htmlspecialchars($t['quantity']) ?></td>
                            <td>MWK <?= htmlspecialchars($t['price']) ?></td>
                            <td><?= htmlspecialchars($t['other_name']) ?></td>
                            <td><?= htmlspecialchars($t['other_phone']) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'pending' && $t['my_confirmation'] === 'pending'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="action" value="confirm">
                                        <input type="hidden" name="transaction_id" value="<?= (int) $t['transaction_id'] ?>">
                                        <button type="submit" name="value" value="yes" class="btn btn-sm btn-success">Sale completed</button>
                                        <button type="submit" name="value" value="no" class="btn btn-sm btn-outline-danger">Not completed</button>
                                    </form>
                                <?php elseif ($t['my_confirmation'] !== 'pending'): ?>
                                    <span class="text-muted small">You said <?= htmlspecialchars($t['my_confirmation']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($as_initiator)): ?>
                        <tr><td colspan="7" class="text-center text-muted">You haven't expressed interest in anything yet - try Market Listings.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small">
        A transaction is marked <strong>Completed</strong> only once both sides confirm the sale went through,
        and <strong>Declined</strong> if either side says it didn't.
    </p>
</div>

</body>
</html>
