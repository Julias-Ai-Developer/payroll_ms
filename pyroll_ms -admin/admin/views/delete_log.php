<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_id'])) {
    $log_id = (int)$_POST['log_id'];

    $stmt = $conn->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->bind_param("i", $log_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Audit log deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete audit log.";
    }
    $stmt->close();
}

header("Location: audit_logs.php");
exit;
