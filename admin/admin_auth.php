<?php
// Shared login + admin check.
// Include this as the FIRST line of every admin_*.php page - it calls
// session_start() itself, so the including page should not call it again.
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// usertype_id 3 = Admin (see admin_panel_setup.sql). This is the real
// security gate - menus only hiding admin links from non-admins is just
// a convenience, not protection on its own.
if (($_SESSION['usertype_id'] ?? null) != 3) {
    header("Location: select_role.php");
    exit;
}
?>
