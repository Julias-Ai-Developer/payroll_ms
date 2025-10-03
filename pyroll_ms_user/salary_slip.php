<?php
session_start();
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
function formatCurrency($amount)
{
    return 'UGX ' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - <?php echo htmlspecialchars($payroll['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }

            .salary-slip {
                box-shadow: none !important;
                border: 1px solid #e5e7eb;
            }
        }

        .watermark {
            position: absolute;
            opacity: 0.03;
            font-size: 8rem;
            font-weight: bold;
            color: #0c4a6e;
            transform: rotate(-45deg);
            z-index: 0;
            pointer-events: none;
            top: 30%;
            left: 10%;
            white-space: nowrap;
        }

        .salary-slip {
            position: relative;
            overflow: hidden;
        }

        .header-pattern {
            background: linear-gradient(135deg, #0ea5e9 0%, #0c4a6e 100%);
            height: 8px;
            width: 100%;
        }

        .company-logo {
            height: 70px;
            width: 70px;
            background: #0ea5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-100 p-6 font-sans">
    <div class="max-w-3xl mx-auto">
        <!-- Print Button -->
        <div class="mb-6 text-right no-print">
            <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-print mr-2"></i> Print Salary Slip
            </button>
            <a href="reports.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reports
            </a>
        </div>

        <!-- Salary Slip -->
        <div class="salary-slip bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header Pattern -->
            <div class="header-pattern"></div>

            <!-- Watermark -->
            <div class="watermark">PAID <?php echo htmlspecialchars($payroll['name']); ?></div>

            <!-- Header -->
            <div class="p-8">
                <div class="flex justify-between items-start mb-6">

                    <div>
                        <h1 class="text-3xl font-bold text-primary-800 mb-1 uppercase tracking-wide"><?php echo htmlspecialchars($_SESSION['business_name']); ?></h1>
                        <h2 class="text-xl font-semibold text-gray-600">SALARY SLIP</h2>
                    </div>
                    <div class="company-logo">
                        PMS
                    </div>
                </div>

                <!-- Employee Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-primary-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-primary-800 mb-3 flex items-center">
                            <i class="fas fa-user mr-2"></i> Employee Information
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employee Name:</span>
                                <span class="font-semibold"><?php echo htmlspecialchars($payroll['name']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employee ID:</span>
                                <span class="font-semibold"><?php echo htmlspecialchars($payroll['emp_id']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Position:</span>
                                <span class="font-semibold"><?php echo htmlspecialchars($payroll['position']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-primary-800 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Payroll Information
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pay Period:</span>
                                <span class="font-semibold"><?php echo htmlspecialchars($payroll['month']) . ' ' . $payroll['year']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Date:</span>
                                <span class="font-semibold"><?php echo date('F d, Y', strtotime($payroll['created_at'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payroll ID:</span>
                                <span class="font-semibold">#<?php echo $payroll_id; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Details -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-primary-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Salary Breakdown
                    </h3>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-primary-600 text-white">
                                    <th class="py-3 px-4 text-left font-medium">Description</th>
                                    <th class="py-3 px-4 text-right font-medium">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                            <span>Gross Salary</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium"><?php echo formatCurrency($payroll['gross_salary']); ?></td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                                            <span>Deductions (Tax & Insurance)</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-red-600">- <?php echo formatCurrency($payroll['deductions']); ?></td>
                                </tr>
                                <tr class="bg-primary-50 font-bold">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-wallet text-primary-600 mr-2"></i>
                                            <span>Net Salary</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-right text-primary-700"><?php echo formatCurrency($payroll['net_salary']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Payment Method</h4>
                        <p class="text-gray-600">Direct Bank Transfer</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Payment Status</h4>
                        <p class="text-green-600 font-medium">Paid</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-gray-300">
                    <div class="flex flex-col md:flex-row justify-between">
                        <div class="mb-4 md:mb-0">
                            <p class="text-gray-600 text-sm mb-1">Employee Signature:</p>
                            <div class="h-12 mt-2 border-b-2 border-dashed border-gray-400 w-48"></div>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Authorized Signature:</p>
                            <div class="h-12 mt-2 border-b-2 border-dashed border-gray-400 w-48"></div>
                        </div>
                    </div>

                    <div class="mt-8 text-center text-sm text-gray-500">
                        <p class="mb-1">This salary is not valid without Employee's and C.E.O's Signature.</p>
                    <p>
                            For any queries regarding this salary slip, please contact
                            <a href="mailto:<?=$_SESSION['business_email']?>"
                                class="text-primary-600 hover:text-primary-800 underline font-semibold transition-colors duration-200">
                                <?=$_SESSION['business_email']?>
                            </a>.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>