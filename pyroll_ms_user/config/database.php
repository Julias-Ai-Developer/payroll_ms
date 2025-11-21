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
        business_id INT(11) NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        UNIQUE KEY (employee_id, month, year)
    )",

    // Deduction types master (per business). Supports fixed, percent, and bracket-based (JSON) methods.
    "CREATE TABLE IF NOT EXISTS deduction_types (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        business_id INT(11) NOT NULL,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(50) NOT NULL,
        method ENUM('fixed','percent','bracket') NOT NULL,
        amount DECIMAL(10,2) DEFAULT 0,
        percent DECIMAL(5,2) DEFAULT 0,
        employer_percent DECIMAL(5,2) DEFAULT 0,
        brackets TEXT NULL,
        statutory TINYINT(1) DEFAULT 0,
        enabled TINYINT(1) DEFAULT 1,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_code_business (code, business_id)
    )",

    // Optional/custom deductions assigned to employees (e.g., loans, penalties, welfare).
    "CREATE TABLE IF NOT EXISTS employee_deductions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        business_id INT(11) NOT NULL,
        employee_id INT(11) NOT NULL,
        deduction_type_id INT(11) NOT NULL,
        custom_amount DECIMAL(10,2) NULL,
        custom_percent DECIMAL(5,2) NULL,
        balance DECIMAL(10,2) NULL,
        active TINYINT(1) DEFAULT 1,
        start_date DATE NULL,
        end_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (deduction_type_id) REFERENCES deduction_types(id) ON DELETE CASCADE
    )",

    // Line items applied per payroll record for audit and reporting.
    "CREATE TABLE IF NOT EXISTS payroll_deductions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        business_id INT(11) NOT NULL,
        payroll_id INT(11) NOT NULL,
        employee_id INT(11) NOT NULL,
        deduction_type_id INT(11) NOT NULL,
        method VARCHAR(20) NOT NULL,
        amount_applied DECIMAL(10,2) NOT NULL,
        employer_amount DECIMAL(10,2) DEFAULT 0,
        meta TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (payroll_id) REFERENCES payroll(id) ON DELETE CASCADE,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (deduction_type_id) REFERENCES deduction_types(id) ON DELETE CASCADE
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