<?php
require_once 'config/database.php';

// Start output buffering
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard</h2>
    
    <?php
    $conn = getConnection();
    
    // Get total employees
    $employeeQuery = "SELECT COUNT(*) as total_employees FROM employees";
    $employeeResult = $conn->query($employeeQuery);
    $totalEmployees = $employeeResult->fetch_assoc()['total_employees'];
    
    // Get total payroll records
    $payrollQuery = "SELECT COUNT(*) as total_payroll FROM payroll";
    $payrollResult = $conn->query($payrollQuery);
    $totalPayroll = $payrollResult->fetch_assoc()['total_payroll'];
    
    // Get total salary paid
    $salaryQuery = "SELECT SUM(net_salary) as total_salary FROM payroll";
    $salaryResult = $conn->query($salaryQuery);
    $totalSalary = $salaryResult->fetch_assoc()['total_salary'] ?: 0;
    
    // Get current month payroll count
    $currentMonth = date('F');
    $currentYear = date('Y');
    $currentMonthQuery = "SELECT COUNT(*) as current_month_count FROM payroll WHERE month = '$currentMonth' AND year = $currentYear";
    $currentMonthResult = $conn->query($currentMonthQuery);
    $currentMonthCount = $currentMonthResult->fetch_assoc()['current_month_count'];
    
    $conn->close();
    ?>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Employees -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Employees</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $totalEmployees; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Total Payroll Records -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Payroll Records</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $totalPayroll; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Total Salary Paid -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Salary Paid</p>
                    <p class="text-2xl font-bold text-gray-800">Ugx<?php echo number_format($totalSalary, 2); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Current Month Payroll -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm"><?php echo $currentMonth; ?> Payrolls</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $currentMonthCount; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activities</h3>
        
        <?php
        $conn = getConnection();
        
        // Get recent payroll entries
        $recentQuery = "SELECT p.id, e.name, p.month, p.year, p.net_salary, p.created_at 
                        FROM payroll p 
                        JOIN employees e ON p.employee_id = e.id 
                        ORDER BY p.created_at DESC 
                        LIMIT 5";
        $recentResult = $conn->query($recentQuery);
        
        if ($recentResult->num_rows > 0) {
            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white">';
            echo '<thead class="bg-gray-100">';
            echo '<tr>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Employee</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Period</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Net Salary</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Date</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $recentResult->fetch_assoc()) {
                echo '<tr class="border-b hover:bg-gray-50">';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['name']) . '</td>';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['month']) . ' ' . $row['year'] . '</td>';
                echo '<td class="py-2 px-4">Ugx ' . number_format($row['net_salary'], 2) . '</td>';
                echo '<td class="py-2 px-4">' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="text-center py-4 text-gray-500">';
            echo '<p>No recent payroll activities found.</p>';
            echo '<p class="mt-2">Start by adding employees and processing payroll.</p>';
            echo '</div>';
        }
        
        $conn->close();
        ?>
    </div>
</div>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>