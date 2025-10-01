<?php
require_once 'config/database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth/login.php");
    exit;
}

// Initialize variables
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

// Generate report based on filters
$conn = getConnection();

$sql = "SELECT p.*, e.name, e.employee_id as emp_id, e.position 
        FROM payroll p 
        JOIN employees e ON p.employee_id = e.id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($month)) {
    $sql .= " AND p.month = ?";
    $params[] = $month;
    $types .= "s";
}

if (!empty($year)) {
    $sql .= " AND p.year = ?";
    $params[] = $year;
    $types .= "i";
}

if (!empty($employee_id)) {
    $sql .= " AND p.employee_id = ?";
    $params[] = $employee_id;
    $types .= "i";
}

$sql .= " ORDER BY p.month, e.name";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
$filename = "payroll_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, [
    'Employee ID', 
    'Employee Name', 
    'Position', 
    'Month', 
    'Year', 
    'Gross Salary', 
    'Deductions', 
    'Net Salary', 
    'Date Processed'
]);

// Fetch and output each row
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['emp_id'],
            $row['name'],
            $row['position'],
            $row['month'],
            $row['year'],
            $row['gross_salary'],
            $row['deductions'],
            $row['net_salary'],
            date('Y-m-d', strtotime($row['created_at']))
        ]);
    }
}

// Close the database connection
$conn->close();

// Close the file pointer
fclose($output);
exit;
?>