<?php
require_once 'config/database.php';

// Initialize variables
$employee_id = $month = $year = "";
$error = $success = "";
$deduction_rate = 0.15; // 15% deduction rate for tax/insurance

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
            $check_sql = "SELECT id FROM payroll WHERE employee_id = ? AND month = ? AND year = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("isi", $employee_id, $month, $year);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Payroll already processed for this employee in $month $year";
            } else {
                // Get employee details
                $emp_sql = "SELECT * FROM employees WHERE id = ?";
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
                    $deductions = $gross_salary * $deduction_rate;
                    $net_salary = $gross_salary - $deductions;
                    
                    // Save payroll record (default status = active)
                    $sql = "INSERT INTO payroll (employee_id, month, year, gross_salary, deductions, net_salary, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'active')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isiddd", $employee_id, $month, $year, $gross_salary, $deductions, $net_salary);
                    
                    if ($stmt->execute()) {
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
                    <span>Deduction Rate: <?php echo $deduction_rate * 100; ?>%</span>
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
                JOIN employees e ON p.employee_id = e.id 
                ORDER BY p.created_at DESC 
                LIMIT 10";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white">';
            echo '<thead class="bg-gray-100">';
            echo '<tr>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Employee</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">ID</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Period</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Gross Salary</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Deductions</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Net Salary</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Status</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Date</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr class="border-b hover:bg-gray-50">';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['name']) . '</td>';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['emp_id']) . '</td>';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['month']) . ' ' . $row['year'] . '</td>';
                echo '<td class="py-2 px-4">Ugx' . number_format($row['gross_salary'], 2) . '</td>';
                echo '<td class="py-2 px-4">Ugx' . number_format($row['deductions'], 2) . '</td>';
                echo '<td class="py-2 px-4">Ugx' . number_format($row['net_salary'], 2) . '</td>';

                // ✅ Status badge
                $status_badge = $row['status'] === 'active'
                    ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>'
                    : '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Inactive</span>';
                echo '<td class="py-2 px-4">' . $status_badge . '</td>';

                echo '<td class="py-2 px-4">' . date('M d, Y', strtotime($row['created_at'])) . '</td>';

                // ✅ Actions
                echo '<td class="py-2 px-4 flex space-x-2">';
                if ($row['status'] === 'active') {
                    echo '<a href="payroll.php?deactivate=' . $row['id'] . '" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded text-sm" 
                            onclick="return confirm(\'Are you sure you want to deactivate this payroll record?\')">Deactivate</a>';
                } else {
                    echo '<a href="payroll.php?activate=' . $row['id'] . '" 
                            class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-sm" 
                            onclick="return confirm(\'Are you sure you want to activate this payroll record?\')">Activate</a>';
                }
                echo '<a href="payroll.php?delete=' . $row['id'] . '" 
                        class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm" 
                        onclick="return confirm(\'Are you sure you want to delete this payroll record?\')">Delete</a>';
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
