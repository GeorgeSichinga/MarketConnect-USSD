<?php
include('admin_auth.php');
include('db_connection.php');
include('logging.php');
$db_conn = connectToDb();

$message = "";
$admin_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_verify') {
        $target_id = (int) $_POST['user_id'];
        $stmt = $db_conn->prepare("UPDATE users SET is_verified = 1 - is_verified WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        logActivity($db_conn, $admin_id, 'admin_toggle_verify', "User #{$target_id}");
        $message = "Verification status updated.";

    } elseif ($action === 'set_usertype') {
        $target_id = (int) $_POST['user_id'];
        $new_type = (int) $_POST['usertype_id'];
        if (in_array($new_type, [1, 2, 3], true)) {
            $stmt = $db_conn->prepare("UPDATE users SET usertype_id = :type WHERE id = :id");
            $stmt->execute(['type' => $new_type, 'id' => $target_id]);
            logActivity($db_conn, $admin_id, 'admin_set_usertype', "User #{$target_id} -> type {$new_type}");
            $message = "User type updated.";
        }
    }
}

$stmt = $db_conn->query("
    SELECT u.id, u.firstname, u.lastname, u.phone_number, u.district, u.usertype_id, u.is_verified,
           ut.Usertype
    FROM users u
    LEFT JOIN usertypes ut ON u.usertype_id = ut.id
    ORDER BY u.id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usertype_labels = [1 => 'Farmer/Seller', 2 => 'Buyer/Trader', 3 => 'Admin'];
$my_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Market Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <p class="mb-2"><a href="admin_panel.php">&larr; Admin panel</a> &middot; <a href="select_role.php">Main menu</a></p>
    <h2 class="text-success mb-3">👤 Manage Users</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card p-3 shadow mb-4">
        <p class="text-muted small mb-2">
            To make a team member an Admin: have them register normally, then set their type to "Admin" below.
        </p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2 align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>District</th>
                        <th>Type</th>
                        <th>Verified</th>
                        <th>Change Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr<?= $u['id'] == $my_id ? ' class="table-success"' : '' ?>>
                            <td><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?><?= $u['id'] == $my_id ? ' (You)' : '' ?></td>
                            <td><?= htmlspecialchars($u['phone_number']) ?></td>
                            <td><?= htmlspecialchars($u['district']) ?></td>
                            <td><?= htmlspecialchars($u['Usertype'] ?? $usertype_labels[$u['usertype_id']] ?? 'Unknown') ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_verify">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $u['is_verified'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                                        <?= $u['is_verified'] ? '✅ Verified' : 'Not verified' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="action" value="set_usertype">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <select name="usertype_id" class="form-select form-select-sm">
                                        <?php foreach ($usertype_labels as $tid => $label): ?>
                                            <option value="<?= $tid ?>" <?= $u['usertype_id'] == $tid ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-success">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
