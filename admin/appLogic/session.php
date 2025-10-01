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
        $admin_id = $_SESSION['admin_id'];
        $sql = "INSERT INTO audit_logs (admin_id, action, details) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $admin_id, $action, $details);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }
    return false;
}
?>