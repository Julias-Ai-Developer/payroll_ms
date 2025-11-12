<?php
session_start();
require_once 'config/database.php';

// Redirect to login if session values are missing
if (!isset($_SESSION['business_id']) || !isset($_SESSION['full_name'])) {
    header("Location: auth/login.php");
    exit();
}

// Assign session variables
$business_id = (int) $_SESSION['business_id']; // cast to int for SQL safety
$full_name   = $_SESSION['full_name'];

// Start output buffering
ob_start();
?>


<div class="mb-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <!-- text-2xl font-bold text-primary-900 flex items-center space-x-3 -->
            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Dashboard Overview</h2>
            <p class="text-slate-600">Welcome back! <span class="font-bold text-primary-600 ml-1"><?=$full_name?></span> Here's what's happening with your payroll today.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <div class="flex items-center space-x-2 text-slate-500 bg-slate-100 px-4 py-2 rounded-lg">
                <i class="fas fa-calendar-day text-primary-500"></i>
                <span><?php echo date('l, F j, Y'); ?></span>
            </div>
        </div>
    </div>
    
    <?php
    $conn = getConnection();
    
    // Get total employees
    $employeeQuery = "SELECT COUNT(*) as total_employees FROM employees WHERE business_id = $business_id";
    $employeeResult = $conn->query($employeeQuery);
    $totalEmployees = $employeeResult->fetch_assoc()['total_employees'];
    
    // Get total payroll records
    $payrollQuery = "SELECT COUNT(*) as total_payroll FROM payroll WHERE business_id = $business_id";
    $payrollResult = $conn->query($payrollQuery);
    $totalPayroll = $payrollResult->fetch_assoc()['total_payroll'];
    
    // Get total salary paid
    $salaryQuery = "SELECT SUM(net_salary) as total_salary FROM payroll WHERE business_id = $business_id";
    $salaryResult = $conn->query($salaryQuery);
    $totalSalary = $salaryResult->fetch_assoc()['total_salary'] ?: 0;
    
    // Get current month payroll count
    $currentMonth = date('F');
    $currentYear = date('Y');
    $currentMonthQuery = "SELECT COUNT(*) as current_month_count FROM payroll WHERE month = '$currentMonth' AND year = $currentYear AND  business_id = $business_id";
    $currentMonthResult = $conn->query($currentMonthQuery);
    $currentMonthCount = $currentMonthResult->fetch_assoc()['current_month_count'];
    
    // Get average salary
    $avgSalaryQuery = "SELECT AVG(net_salary) as avg_salary FROM payroll WHERE business_id = $business_id";
    $avgSalaryResult = $conn->query($avgSalaryQuery);
    $avgSalary = $avgSalaryResult->fetch_assoc()['avg_salary'] ?: 0;
    
    // Get this month's total salary
    $monthSalaryQuery = "SELECT SUM(net_salary) as month_salary FROM payroll WHERE month = '$currentMonth' AND year = $currentYear AND business_id = $business_id";
    $monthSalaryResult = $conn->query($monthSalaryQuery);
    $monthSalary = $monthSalaryResult->fetch_assoc()['month_salary'] ?: 0;
    
    $conn->close();
    ?>
    
    <!-- Enhanced Stats Cards (compact to prevent overlap) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Employees -->
        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-smooth p-4 border border-slate-200 hover:shadow-glow transition-all duration-300 hover-lift group">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-slate-500 text-sm font-medium mb-1">Total Employees</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all"><?php echo $totalEmployees; ?></p>
                    <div class="flex items-center mt-2 text-sm text-slate-500">
                        <i class="fas fa-user-plus text-xs mr-1"></i>
                        <span>Active workforce</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-blue-100 text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Payroll Records -->
        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-smooth p-4 border border-slate-200 hover:shadow-glow transition-all duration-300 hover-lift group">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-slate-500 text-sm font-medium mb-1">Payroll Records</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all"><?php echo $totalPayroll; ?></p>
                    <div class="flex items-center mt-2 text-sm text-slate-500">
                        <i class="fas fa-history text-xs mr-1"></i>
                        <span>All-time records</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Salary Paid -->
        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-smooth p-4 border border-slate-200 hover:shadow-glow transition-all duration-300 hover-lift group">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-slate-500 text-sm font-medium mb-1">Total Salary Paid</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all">UGX <?php echo number_format($totalSalary, 0); ?></p>
                    <div class="flex items-center mt-2 text-sm text-slate-500">
                        <i class="fas fa-wallet text-xs mr-1"></i>
                        <span>All-time expenditure</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-violet-100 text-violet-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Current Month Payroll -->
        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-smooth p-4 border border-slate-200 hover:shadow-glow transition-all duration-300 hover-lift group">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-slate-500 text-sm font-medium mb-1"><?php echo $currentMonth; ?> Payrolls</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all"><?php echo $currentMonthCount; ?></p>
                    <div class="flex items-center mt-2 text-sm text-slate-500">
                        <i class="fas fa-calendar-check text-xs mr-1"></i>
                        <span>This month</span>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-amber-100 text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Average Salary -->
        <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-smooth p-4 border border-blue-100">
            <div class="flex items-center">
                <div class="p-2 rounded-xl bg-blue-100 text-blue-600 mr-3">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Average Salary</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all">UGX <?php echo number_format($avgSalary, 2); ?></p>
                    <p class="text-slate-500 text-xs mt-1">Per employee per month</p>
                </div>
            </div>
        </div>
        
        <!-- This Month's Expenditure -->
        <div class="bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-smooth p-4 border border-emerald-100">
            <div class="flex items-center">
                <div class="p-2 rounded-xl bg-emerald-100 text-emerald-600 mr-3">
                    <i class="fas fa-money-bill text-lg"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium"><?php echo $currentMonth; ?> Expenditure</p>
                    <p class="text-xl font-bold text-slate-800 leading-tight break-all">UGX <?php echo number_format($monthSalary, 2); ?></p>
                    <p class="text-slate-500 text-xs mt-1">This month's payroll</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Recent Activities Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-slate-800">Recent Payroll Activities</h3>
                <a href="payroll.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium flex items-center">
                    View All
                    <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            
            <?php
            $conn = getConnection();
            
            // Get recent payroll entries
            $recentQuery = "SELECT p.id, e.name, p.month, p.year, p.net_salary, p.created_at 
                            FROM payroll p 
                            JOIN employees e ON p.employee_id = e.id  WHERE P.business_id = $business_id
                            ORDER BY p.created_at DESC 
                            LIMIT 6";
            $recentResult = $conn->query($recentQuery);
            
            if ($recentResult->num_rows > 0) {
                echo '<div class="space-y-4">';
                
                while ($row = $recentResult->fetch_assoc()) {
                    echo '<div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors duration-200">';
                    echo '<div class="flex items-center">';
                    echo '<div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center text-primary-600 mr-4">';
                    echo '<i class="fas fa-user"></i>';
                    echo '</div>';
                    echo '<div>';
                    echo '<p class="font-medium text-slate-800">' . htmlspecialchars($row['name']) . '</p>';
                    echo '<p class="text-sm text-slate-500">' . htmlspecialchars($row['month']) . ' ' . $row['year'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="text-right">';
                    echo '<p class="font-semibold text-slate-800 text-sm md:text-base leading-tight break-all">UGX ' . number_format($row['net_salary'], 2) . '</p>';
                    echo '<p class="text-sm text-slate-500">' . date('M d', strtotime($row['created_at'])) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<div class="text-center py-8">';
                echo '<div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">';
                echo '<i class="fas fa-inbox text-slate-400 text-xl"></i>';
                echo '</div>';
                echo '<p class="text-slate-500 font-medium">No recent payroll activities</p>';
                echo '<p class="text-slate-400 text-sm mt-1">Start by adding employees and processing payroll</p>';
                echo '<a href="employees.php" class="inline-flex items-center mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">';
                echo '<i class="fas fa-plus mr-2"></i>';
                echo 'Add Employees';
                echo '</a>';
                echo '</div>';
            }
            
            $conn->close();
            ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
            <h3 class="text-xl font-semibold text-slate-800 mb-6">Quick Actions</h3>
            <div class="space-y-4">
                <a href="employees.php" class="flex items-center p-4 rounded-xl border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition-all duration-200 group">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mr-4 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800">Add New Employee</p>
                        <p class="text-sm text-slate-500">Register a new team member</p>
                    </div>
                </a>
                
                <a href="payroll.php" class="flex items-center p-4 rounded-xl border border-slate-100 hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-200 group">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 mr-4 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-calculator text-lg"></i>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800">Process Payroll</p>
                        <p class="text-sm text-slate-500">Run payroll for this period</p>
                    </div>
                </a>
                
                <a href="reports.php" class="flex items-center p-4 rounded-xl border border-slate-100 hover:bg-purple-50 hover:border-purple-200 transition-all duration-200 group">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 mr-4 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-chart-pie text-lg"></i>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800">View Reports</p>
                        <p class="text-sm text-slate-500">Analytics and insights</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <!-- <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-2xl shadow-smooth p-6 border border-primary-100">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">System Status</h3>
                <p class="text-slate-600">All systems operational. Last updated: <?php echo date('g:i A'); ?></p>
            </div>
            <div class="flex items-center space-x-2 bg-white px-4 py-2 rounded-lg border border-primary-200">
                <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                <span class="text-emerald-600 font-medium">All Systems Normal</span>
            </div>
        </div>
    </div> -->
</div>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>