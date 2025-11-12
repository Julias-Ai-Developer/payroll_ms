<?php
require_once '../../config/database.php';

// Create tables for the Admin module
$tables = [
    "CREATE TABLE IF NOT EXISTS admin_users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS businesses (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        business_name VARCHAR(100) NOT NULL,
        registration_number VARCHAR(50) NOT NULL UNIQUE,
        business_type VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        registration_date DATE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS business_owners (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        business_id INT(11) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        id_number VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        role ENUM('Primary Owner', 'Co-Owner') NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        admin_id INT(11) NOT NULL,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id)
    )",
    
    // Modify existing employees table to include business_id
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS business_id INT(11) AFTER id,
     ADD FOREIGN KEY IF NOT EXISTS (business_id) REFERENCES businesses(id) ON DELETE CASCADE"
];

// Execute each SQL statement
foreach ($tables as $table) {
    if ($conn->query($table) === FALSE) {
        die("Error creating table: " . $conn->error);
    }
}

// Insert default admin user if not exists
$checkAdmin = "SELECT * FROM admin_users WHERE username = 'admin'";
$result = $conn->query($checkAdmin);

if ($result->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $insertAdmin = "INSERT INTO admin_users (username, password, email, full_name) 
                    VALUES ('admin', '$password', 'admin@payroll.com', 'System Administrator')";
    
    if ($conn->query($insertAdmin) === FALSE) {
        die("Error creating default admin: " . $conn->error);
    }
    echo "Default admin user created successfully.<br>";
}

echo "All tables created successfully.";
?>