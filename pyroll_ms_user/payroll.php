<?php
session_start();
$business_id = $_SESSION['business_id'];
require_once 'config/database.php';

// Initialize variables
$employee_id = $month = $year = "";
$error = $success = "";
require_once 'includes/DeductionHelper.php';

// Get current month and year for default values
$current_month = date('F');
$current_year = date('Y');

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $conn = getConnection();
    $payroll_id = $_GET['delete'];
    
    $delete_sql = "DELETE FROM payroll WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $payroll_id);
    
    if ($delete_stmt->execute()) {
        $success = "Payroll record deleted successfully";
    } else {
        $error = "Error deleting payroll record: " . $conn->error;
    }
    
    $conn->close();
}

// Handle deactivate action
if (isset($_GET['deactivate']) && !empty($_GET['deactivate'])) {
    $conn = getConnection();
    $payroll_id = $_GET['deactivate'];
    
    $deactivate_sql = "UPDATE payroll SET status = 'inactive' WHERE id = ?";
    $deactivate_stmt = $conn->prepare($deactivate_sql);
    $deactivate_stmt->bind_param("i", $payroll_id);
    
    if ($deactivate_stmt->execute()) {
        $success = "Payroll record deactivated successfully";
    } else {
        $error = "Error deactivating payroll record: " . $conn->error;
    }
    
    $conn->close();
}

// Handle activate action
if (isset($_GET['activate']) && !empty($_GET['activate'])) {
    $conn = getConnection();
    $payroll_id = $_GET['activate'];
    
    $activate_sql = "UPDATE payroll SET status = 'active' WHERE id = ?";
    $activate_stmt = $conn->prepare($activate_sql);
    $activate_stmt->bind_param("i", $payroll_id);
    
    if ($activate_stmt->execute()) {
        $success = "Payroll record activated successfully";
    } else {
        $error = "Error activating payroll record: " . $conn->error;
    }
    
    $conn->close();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getConnection();
    
    if (isset($_POST['process_payroll'])) {
        // Get form data
        $employee_id = $_POST['employee_id'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        
        // Validate input
        if (empty($employee_id) || empty($month) || empty($year)) {
            $error = "Please select all required fields";
        } else {
            // Check if payroll already exists for this employee and month/year
            $check_sql = "SELECT id FROM payroll WHERE employee_id = ? AND month = ? AND year = ? AND business_id = $business_id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("isi", $employee_id, $month, $year);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Payroll already processed for this employee in $month $year";
            } else {
                // Get employee details
                $emp_sql = "SELECT * FROM employees WHERE id = ? AND business_id = $business_id";
                $emp_stmt = $conn->prepare($emp_sql);
                $emp_stmt->bind_param("i", $employee_id);
                $emp_stmt->execute();
                $emp_result = $emp_stmt->get_result();
                
                if ($emp_result->num_rows == 1) {
                    $employee = $emp_result->fetch_assoc();
                    
                    // Calculate payroll
                    $basic_salary = $employee['basic_salary'];
                    $allowances = $employee['allowances'];
                    $gross_salary = $basic_salary + $allowances;
                    // Compute deductions via helper (statutory + custom)
                    $items = DeductionHelper::computeApplicable($conn, (int)$business_id, (int)$employee_id, (float)$gross_salary);
                    $deductions = 0.0;
                    foreach ($items as $it) {
                        $deductions += floatval($it['employee_amount']);
                    }
                    $deductions = round($deductions, 2);
                    $net_salary = round($gross_salary - $deductions, 2);
                    
                    // Save payroll record (default status = active)
                    $sql = "INSERT INTO payroll (employee_id, month, year, gross_salary, deductions, net_salary,business_id, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isidddd", $employee_id, $month, $year, $gross_salary, $deductions, $net_salary,$business_id);
                    
                    if ($stmt->execute()) {
                        $newPayrollId = $stmt->insert_id;
                        // Record line items
                        DeductionHelper::record($conn, (int)$business_id, (int)$newPayrollId, (int)$employee_id, $items);
                        $success = "Payroll processed successfully for " . $employee['name'] . " - $month $year";
                        // Reset form
                        $employee_id = "";
                    } else {
                        $error = "Error processing payroll: " . $conn->error;
                    }
                } else {
                    $error = "Employee not found";
                }
            }
        }
    }
    
    $conn->close();
}

// Start output buffering
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Payroll Processing</h2>
    
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
    
    <!-- Payroll Processing Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Process New Payroll</h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="employee_id" class="block text-gray-700 text-sm font-bold mb-2">Select Employee*</label>
                    <select id="employee_id" name="employee_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select Employee --</option>
                        <?php
                        $conn = getConnection();
                        $sql = "SELECT id, name, employee_id FROM employees WHERE  business_id = $business_id ORDER BY name ";
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
                
                <div>
                    <label for="month" class="block text-gray-700 text-sm font-bold mb-2">Month*</label>
                    <select id="month" name="month" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <?php
                        $months = [
                            'January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'
                        ];
                        
                        foreach ($months as $m) {
                            $selected = ($m == $current_month) ? 'selected' : '';
                            echo '<option value="' . $m . '" ' . $selected . '>' . $m . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label for="year" class="block text-gray-700 text-sm font-bold mb-2">Year*</label>
                    <select id="year" name="year" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($y == $current_year) ? 'selected' : '';
                            echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" name="process_payroll" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Process Payroll
                </button>
                
                <div class="text-sm text-gray-600">
                    <span>Deductions computed per configured rules</span>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Recent Payroll Records -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Payroll Records</h3>
        
        <?php
        $conn = getConnection();
        $sql = "SELECT p.*, e.name, e.employee_id as emp_id 
                FROM payroll p 
                JOIN employees e ON p.employee_id = e.id WHERE p.business_id = $business_id
                ORDER BY p.created_at DESC 
                LIMIT 10";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full divide-y divide-slate-200 text-sm sortable-table">';
            echo '<thead class="bg-slate-50">';
            echo '<tr>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Employee <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">ID <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="date">Period <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Gross Salary <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Deductions <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Net Salary <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="date">Date <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">';
                echo '<td class="py-3 px-4" data-sort-value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</td>';
                echo '<td class="py-3 px-4" data-sort-value="' . htmlspecialchars($row['emp_id']) . '"><span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-xs">' . htmlspecialchars($row['emp_id']) . '</span></td>';
                echo '<td class="py-3 px-4" data-sort-value="' . strtotime($row['month'] . ' 1, ' . $row['year']) . '">' . htmlspecialchars($row['month']) . ' ' . $row['year'] . '</td>';
                echo '<td class="py-3 px-4 text-right" data-sort-value="' . (float)$row['gross_salary'] . '">UGX ' . number_format($row['gross_salary'], 2) . '</td>';
                echo '<td class="py-3 px-4 text-right" data-sort-value="' . (float)$row['deductions'] . '">UGX ' . number_format($row['deductions'], 2) . '</td>';
                echo '<td class="py-3 px-4 text-right" data-sort-value="' . (float)$row['net_salary'] . '">UGX ' . number_format($row['net_salary'], 2) . '</td>';
                echo '<td class="py-3 px-4" data-sort-value="' . strtotime($row['created_at']) . '">' . date('M d, Y', strtotime($row['created_at'])) . '</td>';

                // ✅ Actions
                echo '<td class="py-3 px-4 whitespace-nowrap" style="white-space: nowrap;">';
                echo '<div class="flex items-center space-x-2">';
                echo '<a href="payroll.php?delete=' . $row['id'] . '" 
                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm" 
                        onclick="return confirm(\'Are you sure you want to delete this payroll record?\')"><i class="fas fa-trash mr-1"></i> Delete</a>';
                echo '</div>';
                echo '</td>';

                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="text-center py-4 text-gray-500">';
            echo '<p>No payroll records found.</p>';
            echo '<p class="mt-2">Process your first payroll using the form above.</p>';
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
