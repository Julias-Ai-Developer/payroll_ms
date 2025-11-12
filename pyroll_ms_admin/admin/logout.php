<?php
session_start();
require_once '../config/database.php';

// Log the logout action if admin is logged in
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $username = $_SESSION['admin_username'];
    
    $action = "Admin logout";
    $details = "Admin user $username logged out";
    
    $sql = "INSERT INTO audit_logs (admin_id, action, details) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iss", $admin_id, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>