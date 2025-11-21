<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'payroll_ms');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS employees (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(20) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        position VARCHAR(100) NOT NULL,
        basic_salary DECIMAL(10,2) NOT NULL,
        allowances DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS payroll (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        employee_id INT(11) NOT NULL,
        month VARCHAR(20) NOT NULL,
        year INT(4) NOT NULL,
        gross_salary DECIMAL(10,2) NOT NULL,
        deductions DECIMAL(10,2) NOT NULL,
        net_salary DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        UNIQUE KEY (employee_id, month, year)
    )"
];

foreach ($tables as $table) {
    if ($conn->query($table) === FALSE) {
        die("Error creating table: " . $conn->error);
    }
}

// Insert default admin user if not exists
$checkAdmin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($checkAdmin);

if ($result->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $insertAdmin = "INSERT INTO users (username, password) VALUES ('admin', '$password')";
    
    if ($conn->query($insertAdmin) === FALSE) {
        die("Error creating default admin: " . $conn->error);
    }
}

// Function to get database connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>