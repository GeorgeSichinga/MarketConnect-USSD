<?php
// Shared activity logging helper - writes to the activity_log table
// (see transactions_setup.sql). Used for the "System Auditability"
// requirement: a record of key actions for monitoring/evaluation.
//
// Wrapped in try/catch so a logging problem never breaks the actual
// feature calling it.
function logActivity($db_conn, $user_id, $action, $details = '') {
    try {
        $stmt = $db_conn->prepare("
            INSERT INTO activity_log (User_ID, Action, Details)
            VALUES (:uid, :action, :details)
        ");
        $stmt->execute([
            'uid'     => $user_id,
            'action'  => $action,
            'details' => $details,
        ]);
    } catch (PDOException $e) {
        // logging should never break the main flow
    }
}
?>
