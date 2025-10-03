<?php
session_start();

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true;
}

// Redirect if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: ../login.php");
        exit;
    }
}

// Log admin action
function logAdminAction($conn, $action, $details = '') {
    if (isAdminLoggedIn()) {
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $admin_username = $_SESSION['admin_username'] ?? 'System';

        $sql = "INSERT INTO audit_logs (admin_id, admin_username, action, details) 
                VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isss", $admin_id, $admin_username, $action, $details);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }
    return false;
}
?>
