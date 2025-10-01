<?php
require_once 'config/database.php';

// Initialize variables
$month = $year = $employee_id = "";
$error = $success = "";
$payroll_data = [];

// Get current month and year for default values
$current_month = date('F');
$current_year = date('Y');

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $month = isset($_POST['month']) ? $_POST['month'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : '';
    
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
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $payroll_data[] = $row;
        }
    } else {
        $error = "No payroll records found for the selected criteria";
    }
    
    $conn->close();
}

// Start output buffering
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Payroll Reports</h2>
    
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Report</h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="month" class="block text-gray-700 text-sm font-bold mb-2">Month</label>
                    <select id="month" name="month" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">-- All Months --</option>
                        <?php
                        $months = [
                            'January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'
                        ];
                        
                        foreach ($months as $m) {
                            $selected = ($m == $month) ? 'selected' : '';
                            echo '<option value="' . $m . '" ' . $selected . '>' . $m . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label for="year" class="block text-gray-700 text-sm font-bold mb-2">Year</label>
                    <select id="year" name="year" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">-- All Years --</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($y == $year) ? 'selected' : '';
                            echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label for="employee_id" class="block text-gray-700 text-sm font-bold mb-2">Employee</label>
                    <select id="employee_id" name="employee_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">-- All Employees --</option>
                        <?php
                        $conn = getConnection();
                        $sql = "SELECT id, name, employee_id FROM employees ORDER BY name";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($employee_id == $row['id']) ? 'selected' : '';
                                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . 
                                     htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['employee_id']) . ')' . 
                                     '</option>';
                            }
                        }
                        $conn->close();
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                    Generate Report
                </button>
                
                <?php if (!empty($payroll_data)): ?>
                <a href="export.php?<?php echo http_build_query($_POST); ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Export to CSV
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Report Results -->
    <?php if (!empty($payroll_data)): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Report Results
            <?php 
            $title_parts = [];
            if (!empty($month)) $title_parts[] = $month;
            if (!empty($year)) $title_parts[] = $year;
            if (!empty($title_parts)) echo ' - ' . implode(' ', $title_parts);
            ?>
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-gray-600">Employee</th>
                        <th class="py-2 px-4 text-left text-gray-600">ID</th>
                        <th class="py-2 px-4 text-left text-gray-600">Position</th>
                        <th class="py-2 px-4 text-left text-gray-600">Period</th>
                        <th class="py-2 px-4 text-left text-gray-600">Gross Salary</th>
                        <th class="py-2 px-4 text-left text-gray-600">Deductions</th>
                        <th class="py-2 px-4 text-left text-gray-600">Net Salary</th>
                        <th class="py-2 px-4 text-left text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_gross = 0;
                    $total_deductions = 0;
                    $total_net = 0;
                    
                    foreach ($payroll_data as $row): 
                        $total_gross += $row['gross_salary'];
                        $total_deductions += $row['deductions'];
                        $total_net += $row['net_salary'];
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['emp_id']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['position']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['month']) . ' ' . $row['year']; ?></td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($row['gross_salary'], 2); ?></td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($row['deductions'], 2); ?></td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($row['net_salary'], 2); ?></td>
                        <td class="py-2 px-4">
                            <a href="salary_slip.php?id=<?php echo $row['id']; ?>" target="_blank" class="text-primary-600 hover:text-primary-800">
                                <i class="fas fa-file-invoice mr-1"></i> Salary Slip
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Totals Row -->
                    <tr class="bg-gray-100 font-bold">
                        <td class="py-2 px-4" colspan="4">Totals</td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($total_gross, 2); ?></td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($total_deductions, 2); ?></td>
                        <td class="py-2 px-4">Ugx <?php echo number_format($total_net, 2); ?></td>
                        <td class="py-2 px-4"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>