<?php
$request_uri = $_SERVER['REQUEST_URI'];

// Redirect to admin login if admin folder requested
if (strpos($request_uri, 'pyroll_ms_admin') !== false) {
    header("Location: /pyroll_ms_admin/admin/login.php");
    exit();
}

// Otherwise redirect to user login
header("Location: /pyroll_ms_user/auth/login.php");
exit();
