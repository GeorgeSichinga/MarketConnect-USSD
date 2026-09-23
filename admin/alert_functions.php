<?php
// Scaffold for the "email alert" system that will later simulate SMS alerts.
//
// Right now this ONLY logs alerts to the alerts_log table (see step1_setup.sql).
// It does not send a real email yet. When you're ready to wire up real
// sending, this is the one place to change: swap the body of sendAlert()
// for a PHPMailer (Gmail SMTP) call, and it will apply everywhere
// queueAlert()/sendAlert() are already being called from.

function queueAlert($db_conn, $user_id, $alert_type, $message) {
    $stmt = $db_conn->prepare("
        INSERT INTO alerts_log (User_ID, Alert_Type, Message, Status)
        VALUES (:uid, :type, :message, 'pending')
    ");
    $stmt->execute([
        'uid'     => $user_id,
        'type'    => $alert_type,
        'message' => $message,
    ]);
    return $db_conn->lastInsertId();
}

// Placeholder "sender". For now it just marks the alert as sent in the log
// so you can see the flow working end to end in the alerts_log table.
// Later: replace the body with a real PHPMailer send, and set Status to
// 'sent' only if the email actually went out (or 'failed' if not).
function sendAlert($db_conn, $alert_id) {
    $stmt = $db_conn->prepare("UPDATE alerts_log SET Status = 'sent' WHERE Alert_ID = :id");
    $stmt->execute(['id' => $alert_id]);
}
?>
