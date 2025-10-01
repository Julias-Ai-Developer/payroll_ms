<?php
require_once 'config/database.php';

// Check if payroll ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No payroll record specified");
}

$payroll_id = $_GET['id'];
$conn = getConnection();

// Get payroll and employee details
$sql = "SELECT p.*, e.name, e.employee_id as emp_id, e.position 
        FROM payroll p 
        JOIN employees e ON p.employee_id = e.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payroll_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Error: Payroll record not found");
}

$payroll = $result->fetch_assoc();
$conn->close();

// Format currency
function formatCurrency($amount) {
    return 'Ugx' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - <?php echo htmlspecialchars($payroll['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Print Button -->
        <div class="mb-4 text-right no-print">
            <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-print mr-1"></i> Print Salary Slip
            </button>
            <a href="reports.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Back to Reports
            </a>
        </div>
        
        <!-- Salary Slip -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <!-- Header -->
            <div class="border-b pb-4 mb-6">
                <h1 class="text-2xl font-bold text-center text-primary-800 mb-2">PAYROLL MANAGEMENT SYSTEM</h1>
                <h2 class="text-xl font-semibold text-center text-gray-700">SALARY SLIP</h2>
            </div>
            
            <!-- Employee Info -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-gray-600">Employee Name:</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($payroll['name']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Employee ID:</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($payroll['emp_id']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Position:</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($payroll['position']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Pay Period:</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($payroll['month']) . ' ' . $payroll['year']; ?></p>
                </div>
            </div>
            
            <!-- Salary Details -->
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left text-gray-700 font-semibold">Description</th>
                            <th class="py-2 px-4 text-right text-gray-700 font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t">
                            <td class="py-3 px-4">Gross Salary</td>
                            <td class="py-3 px-4 text-right"><?php echo formatCurrency($payroll['gross_salary']); ?></td>
                        </tr>
                        <tr class="border-t bg-red-50">
                            <td class="py-3 px-4">Deductions (Tax & Insurance)</td>
                            <td class="py-3 px-4 text-right text-red-600">- <?php echo formatCurrency($payroll['deductions']); ?></td>
                        </tr>
                        <tr class="border-t bg-gray-100 font-bold">
                            <td class="py-3 px-4">Net Salary</td>
                            <td class="py-3 px-4 text-right"><?php echo formatCurrency($payroll['net_salary']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 pt-4 border-t">
                <div class="flex justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Date Issued:</p>
                        <p class="font-semibold"><?php echo date('F d, Y', strtotime($payroll['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Authorized Signature:</p>
                        <div class="h-10 mt-2 border-b border-gray-400 w-40"></div>
                    </div>
                </div>
                
                <div class="mt-8 text-center text-sm text-gray-500">
                    <p>This is a computer-generated document. No signature is required.</p>
                    <p class="mt-1">For any queries regarding this salary slip, please contact the HR department.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>