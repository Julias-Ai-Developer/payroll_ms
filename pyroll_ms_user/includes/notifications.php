<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$response = [
    'count' => 0,
    'items' => [],
];

if (!isset($_SESSION['business_id'])) {
    echo json_encode($response);
    exit;
}

$business_id = (int)$_SESSION['business_id'];

try {
    $conn = getConnection();
    // Recent payroll entries as notifications
    $sql = "SELECT p.id, e.name, p.month, p.year, p.net_salary, p.created_at
            FROM payroll p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.business_id = $business_id
            ORDER BY p.created_at DESC
            LIMIT 6";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $response['items'][] = [
                'id' => (int)$row['id'],
                'title' => 'Payroll processed',
                'message' => htmlspecialchars($row['name']) . ' — ' . htmlspecialchars($row['month']) . ' ' . (int)$row['year'],
                'amount' => number_format((float)$row['net_salary'], 2),
                'created_at' => date('M d, g:i A', strtotime($row['created_at'])),
                'url' => 'payroll.php',
            ];
        }
        $response['count'] = count($response['items']);
    }
    $conn->close();
} catch (Throwable $e) {
    // Fail silently, return empty response
}

echo json_encode($response);
?>