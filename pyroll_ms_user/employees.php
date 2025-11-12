<?php
session_start();
$business_id = $_SESSION['business_id'];
$business_name = isset($_SESSION['business_name']) ? $_SESSION['business_name'] : 'Business';
require_once 'config/database.php';

// Initialize variables
$name = $position = $employee_id = "";
$basic_salary = $allowances = 0;
$error = $success = "";
$edit_id = null;

// Helper: Generate next auto employee ID for this business
function generateNextEmployeeId($conn, $business_id, $business_name) {
    $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $business_name), 0, 2));
    if (strlen($prefix) < 2) {
        $prefix = strtoupper(str_pad($prefix, 2, 'X'));
    }

    $sql = "SELECT MAX(CAST(SUBSTRING(employee_id, 3) AS UNSIGNED)) AS max_num FROM employees WHERE business_id = ? AND employee_id LIKE CONCAT(?, '%')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $business_id, $prefix);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $maxNum = isset($row['max_num']) ? (int)$row['max_num'] : 0;
    $nextNum = $maxNum + 1;
    $formatted = sprintf('%04d', $nextNum);
    return $prefix . $formatted;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getConnection();

    // For employee creation or update
    if (isset($_POST['save_employee'])) {
        // Get form data (employee_id will be auto-generated for new records)
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        // Sanitize formatted currency inputs (remove commas before parsing)
        $basic_salary_raw = isset($_POST['basic_salary']) ? str_replace(',', '', $_POST['basic_salary']) : '0';
        $allowances_raw = isset($_POST['allowances']) ? str_replace(',', '', $_POST['allowances']) : '0';
        $basic_salary = floatval($basic_salary_raw);
        $allowances = floatval($allowances_raw);

        // Validate input
        if (empty($name) || empty($position) || $basic_salary <= 0) {
            $error = "Please fill all required fields with valid data";
        } else {
            // Check if we're updating or creating
            if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
                // Update existing employee
                $id = $_POST['edit_id'];
                // Update existing employee (employee_id remains unchanged)
                $sql = "UPDATE employees SET name = ?, position = ?, basic_salary = ?, allowances = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdi", $name, $position, $basic_salary, $allowances, $id);

                if ($stmt->execute()) {
                    $success = "Employee updated successfully";
                    // Reset form fields
                    $name = $position = $employee_id = "";
                    $basic_salary = $allowances = 0;
                    $edit_id = null;
                } else {
                    $error = "Error updating employee: " . $conn->error;
                }
            } else {
                // Create new employee with auto-generated ID
                $employee_id = generateNextEmployeeId($conn, $business_id, $business_name);

                // Extra safety: ensure not already taken
                $check_sql = "SELECT id FROM employees WHERE business_id = ? AND employee_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("is", $business_id, $employee_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error = "Employee ID collision occurred. Please try again.";
                } else {
                    $sql = "INSERT INTO employees (employee_id, name, position, business_id, basic_salary, allowances) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssidd", $employee_id, $name, $position, $business_id, $basic_salary, $allowances);

                    if ($stmt->execute()) {
                        $success = "Employee added successfully. You can now assign deductions below.";
                        $newEmpId = $stmt->insert_id;

                        // Assign any default deductions selected during creation
                        $defaults = isset($_POST['default_deductions']) && is_array($_POST['default_deductions']) ? $_POST['default_deductions'] : [];
                        if (!empty($defaults)) {
                            foreach ($defaults as $did) {
                                $didInt = intval($did);
                                if ($didInt > 0) {
                                    $ins = $conn->prepare("INSERT INTO employee_deductions (business_id, employee_id, deduction_type_id, custom_amount, custom_percent, balance, active, start_date, end_date) VALUES (?,?,?,?,?,?,?,?,?)");
                                    $null = null; $one = 1; $sd = null; $ed = null;
                                    $ins->bind_param("iiiiddiss", $business_id, $newEmpId, $didInt, $null, $null, $null, $one, $sd, $ed);
                                    $ins->execute();
                                    $ins->close();
                                }
                            }
                        }

                        // Load new employee into edit mode for immediate deduction assignment
                        $edit_id = $newEmpId;
                        $load = $conn->prepare("SELECT * FROM employees WHERE id = ?");
                        $load->bind_param("i", $edit_id);
                        $load->execute();
                        $loaded = $load->get_result();
                        if ($loaded && $loaded->num_rows === 1) {
                            $row = $loaded->fetch_assoc();
                            $employee_id = $row['employee_id'];
                            $name = $row['name'];
                            $position = $row['position'];
                            $basic_salary = $row['basic_salary'];
                            $allowances = $row['allowances'];
                        }
                        $load->close();
                    } else {
                        $error = "Error adding employee: " . $conn->error;
                    }
                }
            }
        }
    }

    // For employee deletion
    if (isset($_POST['delete_employee'])) {
        $id = $_POST['delete_id'];

        // Check if employee has payroll records
        $check_sql = "SELECT id FROM payroll WHERE employee_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Cannot delete employee with existing payroll records";
        } else {
            // Delete employee
            $sql = "DELETE FROM employees WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $success = "Employee deleted successfully";
            } else {
                $error = "Error deleting employee: " . $conn->error;
            }
        }
    }

    // For employee edit (loading data into form)
    if (isset($_POST['edit_employee'])) {
        $edit_id = $_POST['edit_id'];

        $sql = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $employee_id = $row['employee_id'];
            $name = $row['name'];
            $position = $row['position'];
            $basic_salary = $row['basic_salary'];
            $allowances = $row['allowances'];
        }
    }

    // Assign a deduction to an employee (optional/custom types)
    if (isset($_POST['assign_deduction'])) {
        $emp_id_ref = isset($_POST['employee_ref_id']) ? intval($_POST['employee_ref_id']) : 0;
        $deduction_type_id = isset($_POST['deduction_type_id']) ? intval($_POST['deduction_type_id']) : 0;
        $custom_amount_raw = isset($_POST['custom_amount']) ? str_replace(',', '', $_POST['custom_amount']) : '';
        $custom_percent_raw = isset($_POST['custom_percent']) ? str_replace(',', '', $_POST['custom_percent']) : '';
        $balance_raw = isset($_POST['balance']) ? str_replace(',', '', $_POST['balance']) : '';
        $custom_amount = strlen($custom_amount_raw) ? floatval($custom_amount_raw) : null;
        $custom_percent = strlen($custom_percent_raw) ? floatval($custom_percent_raw) : null;
        $balance = strlen($balance_raw) ? floatval($balance_raw) : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $active = isset($_POST['active']) ? 1 : 1;

        if ($emp_id_ref <= 0 || $deduction_type_id <= 0) {
            $error = "Select employee and deduction type.";
        } else {
            // Prevent duplicates for same type
            $dup = $conn->prepare("SELECT id FROM employee_deductions WHERE business_id = ? AND employee_id = ? AND deduction_type_id = ?");
            $dup->bind_param("iii", $business_id, $emp_id_ref, $deduction_type_id);
            $dup->execute();
            $existRes = $dup->get_result();
            $dup->close();

            if ($existRes && $existRes->num_rows > 0) {
                $error = "This deduction is already assigned to the employee.";
            } else {
                $ins = $conn->prepare("INSERT INTO employee_deductions (business_id, employee_id, deduction_type_id, custom_amount, custom_percent, balance, active, start_date, end_date) VALUES (?,?,?,?,?,?,?,?,?)");
                $ins->bind_param("iiiiddiss", $business_id, $emp_id_ref, $deduction_type_id, $custom_amount, $custom_percent, $balance, $active, $start_date, $end_date);
                if ($ins->execute()) {
                    $success = "Deduction assigned to employee.";
                } else {
                    $error = "Failed to assign deduction: " . $conn->error;
                }
                $ins->close();
            }
        }
    }

    // Toggle employee deduction active state
    if (isset($_POST['toggle_assignment'])) {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $new_state = intval($_POST['new_state'] ?? 1);
        $upd = $conn->prepare("UPDATE employee_deductions SET active = ? WHERE id = ? AND business_id = ?");
        $upd->bind_param("iii", $new_state, $assignment_id, $business_id);
        if ($upd->execute()) {
            $success = "Deduction status updated.";
        } else {
            $error = "Failed to update status: " . $conn->error;
        }
        $upd->close();
    }

    // Delete employee deduction assignment
    if (isset($_POST['delete_assignment'])) {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $del = $conn->prepare("DELETE FROM employee_deductions WHERE id = ? AND business_id = ?");
        $del->bind_param("ii", $assignment_id, $business_id);
        if ($del->execute()) {
            $success = "Deduction assignment deleted.";
        } else {
            $error = "Failed to delete deduction assignment: " . $conn->error;
        }
        $del->close();
    }

    // Update employee deduction assignment (edit values)
    if (isset($_POST['update_assignment'])) {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $custom_amount_raw = isset($_POST['custom_amount']) ? str_replace(',', '', $_POST['custom_amount']) : '';
        $custom_percent_raw = isset($_POST['custom_percent']) ? str_replace(',', '', $_POST['custom_percent']) : '';
        $balance_raw = isset($_POST['balance']) ? str_replace(',', '', $_POST['balance']) : '';
        $custom_amount = strlen($custom_amount_raw) ? floatval($custom_amount_raw) : null;
        $custom_percent = strlen($custom_percent_raw) ? floatval($custom_percent_raw) : null;
        $balance = strlen($balance_raw) ? floatval($balance_raw) : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $active = isset($_POST['active']) ? 1 : 0;

        if ($assignment_id <= 0) {
            $error = "Invalid assignment selected.";
        } else {
            $upd = $conn->prepare("UPDATE employee_deductions SET custom_amount = ?, custom_percent = ?, balance = ?, start_date = ?, end_date = ?, active = ? WHERE id = ? AND business_id = ?");
            $upd->bind_param("dddssiii", $custom_amount, $custom_percent, $balance, $start_date, $end_date, $active, $assignment_id, $business_id);
            if ($upd->execute()) {
                $success = "Deduction assignment updated.";
            } else {
                $error = "Failed to update assignment: " . $conn->error;
            }
            $upd->close();
        }
    }

    $conn->close();
}

// Prefill auto-generated ID for new employee when not editing
if ($edit_id === null && empty($employee_id)) {
    $conn = getConnection();
    $employee_id = generateNextEmployeeId($conn, $business_id, $business_name);
    $conn->close();
}

// Start output buffering
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Employee Management</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 page-alert" role="alert" data-auto-dismiss="true" data-dismiss-time="5000">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 page-alert" role="alert" data-auto-dismiss="true" data-dismiss-time="5000">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <!-- Employee Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <?php echo $edit_id ? 'Edit Employee' : 'Add New Employee'; ?>
        </h3>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php if ($edit_id): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="employee_id" class="block text-gray-700 text-sm font-bold mb-2">Employee ID*</label>
                    <input type="text" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($employee_id); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-slate-100 cursor-not-allowed" readonly>
                    <p class="text-xs text-slate-500 mt-1">Auto-generated: first two letters of business + 4-digit number.</p>
                </div>

                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name*</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div>
                    <label for="position" class="block text-gray-700 text-sm font-bold mb-2">Position*</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($position); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div>
                    <label for="basic_salary" class="block text-gray-700 text-sm font-bold mb-2">Basic Salary*</label>
                    <input type="text" id="basic_salary" name="basic_salary" value="<?php echo htmlspecialchars(number_format((float)$basic_salary, 2)); ?>" inputmode="decimal" pattern="[0-9,\.]*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="8,900,000,000.00" required autocomplete="off">
                </div>

                <div>
                    <label for="allowances" class="block text-gray-700 text-sm font-bold mb-2">Allowances</label>
                    <input type="text" id="allowances" name="allowances" placeholder="50,000.00" value="<?php echo htmlspecialchars(number_format((float)$allowances, 2)); ?>" inputmode="decimal" pattern="[0-9,\.]*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" autocomplete="off">
                </div>
            </div>

            <?php if (!$edit_id): ?>
            <!-- Default Deductions (optional, assigned on creation) -->
            <div class="border rounded p-4 mb-4">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Default Deductions (optional)</h4>
                <?php
                    $connTypes = getConnection();
                    $typesStmt = $connTypes->prepare("SELECT id, name, code FROM deduction_types WHERE business_id = ? AND enabled = 1 AND statutory = 0 ORDER BY name");
                    $typesStmt->bind_param("i", $business_id);
                    $typesStmt->execute();
                    $typesRes = $typesStmt->get_result();
                ?>
                <?php if ($typesRes->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <?php while ($t = $typesRes->fetch_assoc()): ?>
                            <label class="inline-flex items-center space-x-2 py-1">
                                <input type="checkbox" name="default_deductions[]" value="<?php echo (int)$t['id']; ?>" class="form-checkbox">
                                <span><?php echo htmlspecialchars($t['name']); ?> <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($t['code']); ?>)</span></span>
                            </label>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-600">No optional deduction types are enabled. Configure them under Deductions.</p>
                <?php endif; ?>
                <?php $typesStmt->close(); $connTypes->close(); ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center justify-between">
                <button type="submit" name="save_employee" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo $edit_id ? 'Update Employee' : 'Add Employee'; ?>
                </button>

                <?php if ($edit_id): ?>
                    <a href="employees.php" class="text-primary-600 hover:text-primary-800">
                        Cancel Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($edit_id): ?>
    <!-- Employee Deduction Assignments -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Deduction Assignments</h3>
        <?php
            $conn = getConnection();
            // Fetch enabled, non-statutory types
            $typeStmt = $conn->prepare("SELECT id, name, code, method FROM deduction_types WHERE business_id = ? AND enabled = 1 AND statutory = 0 ORDER BY name");
            $typeStmt->bind_param("i", $business_id);
            $typeStmt->execute();
            $typeRes = $typeStmt->get_result();
        ?>
        <form method="post" class="mb-4">
            <input type="hidden" name="employee_ref_id" value="<?php echo (int)$edit_id; ?>" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Deduction Type*</label>
                    <select name="deduction_type_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select Type --</option>
                        <?php while ($t = $typeRes->fetch_assoc()): ?>
                            <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?> (<?php echo htmlspecialchars($t['code']); ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Custom Amount (UGX)</label>
                    <input type="text" name="custom_amount" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" placeholder="e.g., 50,000" />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Percent (%)</label>
                    <input type="text" name="custom_percent" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" placeholder="e.g., 10" />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Balance (for loans)</label>
                    <input type="text" name="balance" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" placeholder="e.g., 2,000,000" />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                    <input type="date" name="start_date" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                    <input type="date" name="end_date" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <label class="inline-flex items-center"><input type="checkbox" name="active" class="mr-2" checked /> Active</label>
                <button type="submit" name="assign_deduction" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">Assign Deduction</button>
            </div>
        </form>
        <?php $typeStmt->close(); ?>

        <?php
            // Existing assignments
            $assStmt = $conn->prepare("SELECT ed.id, ed.custom_amount, ed.custom_percent, ed.balance, ed.active, ed.start_date, ed.end_date, dt.name, dt.code, dt.method FROM employee_deductions ed JOIN deduction_types dt ON ed.deduction_type_id = dt.id WHERE ed.business_id = ? AND ed.employee_id = ? ORDER BY dt.name");
            $assStmt->bind_param("ii", $business_id, $edit_id);
            $assStmt->execute();
            $assRes = $assStmt->get_result();
        ?>
        <div class="overflow-x-auto">
            <table id="assignmentsTable" class="min-w-full divide-y divide-slate-200 text-sm sortable-table">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Type <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Method <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Amount/Percent <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Balance <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-center text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Active <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="date">Period <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                        <th class="py-3 px-4 text-left text-slate-700 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($assRes->num_rows > 0): while ($a = $assRes->fetch_assoc()): ?>
                    <tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">
                        <td class="py-3 px-4" data-sort-value="<?php echo htmlspecialchars($a['name']); ?>"><?php echo htmlspecialchars($a['name']); ?>
                          <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-xs ml-1"><?php echo htmlspecialchars($a['code']); ?></span>
                        </td>
                        <td class="py-3 px-4">
                          <?php 
                            $m = $a['method'];
                            $cls = ($m==='fixed') ? 'bg-sky-100 text-sky-700' : (($m==='percent') ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700');
                            $lbl = ($m==='fixed') ? 'Fixed Amount' : (($m==='percent') ? 'Percentage' : 'Bracket');
                          ?>
                          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?php echo $cls; ?>"><?php echo $lbl; ?></span>
                        </td>
                        <?php $amountSort = (!is_null($a['custom_amount'])) ? (float)$a['custom_amount'] : ((!is_null($a['custom_percent'])) ? (float)$a['custom_percent'] : -1); ?>
                        <td class="py-3 px-4 text-right" data-sort-value="<?php echo $amountSort; ?>">
                            <?php
                                if (!is_null($a['custom_amount'])) echo 'UGX ' . number_format($a['custom_amount'], 2);
                                elseif (!is_null($a['custom_percent'])) echo number_format($a['custom_percent'], 2) . '%';
                                else echo '-';
                            ?>
                        </td>
                        <td class="py-3 px-4 text-right" data-sort-value="<?php echo !is_null($a['balance']) ? (float)$a['balance'] : -1; ?>"><?php echo !is_null($a['balance']) ? 'UGX ' . number_format($a['balance'], 2) : '<span class="text-slate-400">&mdash;</span>'; ?></td>
                        <td class="py-3 px-4 text-center" data-sort-value="<?php echo $a['active'] ? 1 : 0; ?>">
                          <?php if ($a['active']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"><i class="fas fa-check-circle mr-1"></i> Active</span>
                          <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                          <?php endif; ?>
                        </td>
                        <td class="py-3 px-4" data-sort-value="<?php echo $a['start_date'] ? strtotime($a['start_date']) : 0; ?>">
                            <?php echo ($a['start_date'] ? htmlspecialchars($a['start_date']) : '-') . ' — ' . ($a['end_date'] ? htmlspecialchars($a['end_date']) : '-'); ?>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap" style="white-space: nowrap;">
                            <div class="flex items-center space-x-2">
                            <button type="button" class="inline-flex items-center bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm mr-2 toggle-edit-btn" data-assignment-id="<?php echo (int)$a['id']; ?>">
                                <i class="fas fa-pen mr-1"></i> Edit
                            </button>
                            <form method="post" class="inline mr-2">
                                <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>" />
                                <input type="hidden" name="new_state" value="<?php echo $a['active'] ? 0 : 1; ?>" />
                                <button type="submit" name="toggle_assignment" class="inline-flex items-center <?php echo $a['active'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700'; ?> text-white text-xs font-semibold px-3 py-1 rounded shadow-sm">
                                    <i class="fas <?php echo $a['active'] ? 'fa-toggle-off' : 'fa-toggle-on'; ?> mr-1"></i>
                                    <?php echo $a['active'] ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                            <form method="post" class="inline">
                                <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>" />
                                <button type="submit" name="delete_assignment" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <!-- Inline Edit Row -->
                    <tr class="bg-gray-50 border-b edit-row" id="edit-row-<?php echo (int)$a['id']; ?>" style="display:none;">
                        <td colspan="7" class="py-3 px-4">
                            <form method="post" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                                <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>" />
                                <div>
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Custom Amount (UGX)</label>
                                    <input type="text" name="custom_amount" value="<?php echo is_null($a['custom_amount']) ? '' : htmlspecialchars(number_format((float)$a['custom_amount'], 2)); ?>" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Percent (%)</label>
                                    <input type="text" name="custom_percent" value="<?php echo is_null($a['custom_percent']) ? '' : htmlspecialchars(number_format((float)$a['custom_percent'], 2)); ?>" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Balance</label>
                                    <input type="text" name="balance" value="<?php echo is_null($a['balance']) ? '' : htmlspecialchars(number_format((float)$a['balance'], 2)); ?>" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-xs font-bold mb-1">Start Date</label>
                                    <input type="date" name="start_date" value="<?php echo $a['start_date'] ? htmlspecialchars($a['start_date']) : ''; ?>" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-xs font-bold mb-1">End Date</label>
                                    <input type="date" name="end_date" value="<?php echo $a['end_date'] ? htmlspecialchars($a['end_date']) : ''; ?>" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="inline-flex items-center mr-3"><input type="checkbox" name="active" class="mr-2" <?php echo $a['active'] ? 'checked' : ''; ?> /> Active</label>
                                    <button type="submit" name="update_assignment" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm"><i class="fas fa-save mr-1"></i> Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="py-3 px-4 text-center text-gray-500">No assignments yet. Add a deduction above.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php $assStmt->close(); $conn->close(); ?>
    </div>
    <?php endif; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Auto-dismiss inline page alerts (success/error) after 5 seconds with fade-out
                document.querySelectorAll('[role="alert"].page-alert[data-auto-dismiss="true"]').forEach(function(alertEl) {
                    var timeout = parseInt(alertEl.getAttribute('data-dismiss-time') || '5000', 10);
                    setTimeout(function() {
                        alertEl.style.transition = 'opacity 400ms ease-out, transform 400ms ease-out';
                        alertEl.style.opacity = '0';
                        alertEl.style.transform = 'translateY(-4px)';
                        setTimeout(function() {
                            if (alertEl && alertEl.parentNode) {
                                alertEl.parentNode.removeChild(alertEl);
                            }
                        }, 450);
                    }, timeout);
                });

                document.querySelectorAll('.toggle-edit-btn').forEach(function(btn) {
                    btn.addEventListener('click', function () {
                        var id = btn.getAttribute('data-assignment-id');
                        var row = document.getElementById('edit-row-' + id);
                        if (!row) return;
                        var current = row.style.display;
                        row.style.display = (!current || current === 'none') ? 'table-row' : 'none';
                    });
                });
            });
        </script>
        <!-- Employee List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Employee List</h3>

        <?php
        $conn = getConnection();
        $sql = "SELECT * FROM employees WHERE business_id = ? ORDER BY name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="overflow-x-auto">';
            echo '<table id="employeeListTable" class="min-w-full divide-y divide-slate-200 text-sm sortable-table">';
            echo '<thead class="bg-slate-50">';
            echo '<tr>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">ID <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Name <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Position <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Basic Salary <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Allowances <i class="fas fa-sort ml-1 text-slate-400"></i></th>';
            echo '<th class="py-3 px-4 text-left text-slate-700 font-semibold">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">';
                echo '<td class="py-3 px-4" data-sort-value="' . htmlspecialchars($row['employee_id']) . '"><span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-xs">' . htmlspecialchars($row['employee_id']) . '</span></td>';
                echo '<td class="py-3 px-4" data-sort-value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</td>';
                echo '<td class="py-3 px-4" data-sort-value="' . htmlspecialchars($row['position']) . '">' . htmlspecialchars($row['position']) . '</td>';
                echo '<td class="py-3 px-4 text-right" data-sort-value="' . (float)$row['basic_salary'] . '">UGX ' . number_format($row['basic_salary'], 2) . '</td>';
                echo '<td class="py-3 px-4 text-right" data-sort-value="' . (float)$row['allowances'] . '">UGX ' . number_format($row['allowances'], 2) . '</td>';
                echo '<td class="py-3 px-4 whitespace-nowrap" style="white-space: nowrap;">';

                // Edit form
echo '<div class="flex items-center space-x-2">';
echo '<form method="post" class="inline-block">';
echo '<input type="hidden" name="edit_id" value="' . $row['id'] . '">';
echo '<button type="submit" name="edit_employee" class="inline-flex items-center bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm" title="Edit">';
echo '<i class="fas fa-pen mr-1"></i> Edit';
echo '</button>';
echo '</form>';

// Delete form with custom modal trigger
echo '<form method="post" class="inline-block">';
echo '<input type="hidden" name="delete_id" value="' . $row['id'] . '">';
echo '<button type="button" data-action="delete-employee" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm" title="Delete">';
echo '<i class="fas fa-trash mr-1"></i> Delete';
echo '</button>';
echo '<input type="hidden" name="delete_employee" value="1">';
echo '</form>';
echo '</div>';

                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="text-center py-4 text-gray-500">';
            echo '<p>No employees found.</p>';
            echo '<p class="mt-2">Add your first employee using the form above.</p>';
            echo '</div>';
        }

        $conn->close();
        ?>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div id="empConfirmModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-slate-800">Delete Employee</h3>
      <button id="empConfirmClose" class="text-slate-500 hover:text-slate-800"><i class="fas fa-times"></i></button>
    </div>
    <p class="text-slate-600">This action cannot be undone. Delete this employee?</p>
    <div class="mt-6 flex justify-end space-x-2">
      <button id="empConfirmCancel" class="border border-slate-300 text-slate-700 py-2 px-4 rounded hover:bg-slate-50">Cancel</button>
      <button id="empConfirmProceed" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded">Delete</button>
    </div>
  </div>
</div>

<script>
  function toggle(el, show) {
    if (!el) return;
    if (show) { el.classList.remove('hidden'); el.classList.add('flex'); }
    else { el.classList.add('hidden'); el.classList.remove('flex'); }
  }
  const empModal = document.getElementById('empConfirmModal');
  const empClose = document.getElementById('empConfirmClose');
  const empCancel = document.getElementById('empConfirmCancel');
  const empProceed = document.getElementById('empConfirmProceed');
  let currentDeleteForm = null;

  document.querySelectorAll('[data-action="delete-employee"]').forEach(btn => {
    btn.addEventListener('click', () => {
      currentDeleteForm = btn.closest('form');
      toggle(empModal, true);
    });
  });

  [empClose, empCancel].forEach(b => b && b.addEventListener('click', () => toggle(empModal, false)));
  empProceed && empProceed.addEventListener('click', () => {
    if (currentDeleteForm) currentDeleteForm.submit();
  });
</script>

<script>
  // Live format currency inputs with thousand separators
  (function() {
    const ids = ['basic_salary', 'allowances'];

    function formatCurrency(value) {
      if (typeof value !== 'string') value = String(value ?? '');
      // Remove invalid chars
      let cleaned = value.replace(/[^0-9.]/g, '');
      // Keep only first dot
      const firstDot = cleaned.indexOf('.');
      if (firstDot !== -1) {
        cleaned = cleaned.slice(0, firstDot + 1) + cleaned.slice(firstDot + 1).replace(/\./g, '');
      }
      // Split integer and decimal
      const parts = cleaned.split('.');
      const integer = parts[0];
      let decimal = parts[1] ?? '';
      // Limit to 2 decimal places
      if (decimal.length > 2) decimal = decimal.slice(0, 2);
      // Add thousand separators to integer part
      const withCommas = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return decimal.length ? withCommas + '.' + decimal : withCommas;
    }

    function attachFormatting(input) {
      if (!input) return;
      // Initial format on load
      input.value = formatCurrency(input.value);

      input.addEventListener('input', function() {
        const cursorEnd = this.selectionEnd;
        const beforeLen = this.value.length;
        this.value = formatCurrency(this.value);
        const afterLen = this.value.length;
        // Best-effort caret adjustment
        const diff = afterLen - beforeLen;
        const newPos = (cursorEnd || afterLen) + diff;
        try { this.setSelectionRange(newPos, newPos); } catch(e) {}
      });

      input.addEventListener('blur', function() {
        this.value = formatCurrency(this.value);
      });
    }

    ids.forEach(id => attachFormatting(document.getElementById(id)));

    // Ensure sanitized values on submit as an extra guard
    const salaryInput = document.getElementById('basic_salary');
    if (salaryInput && salaryInput.form) {
      salaryInput.form.addEventListener('submit', function() {
        ids.forEach(id => {
          const el = document.getElementById(id);
          if (el && typeof el.value === 'string') {
            // Strip commas for submission; keep decimal
            el.value = el.value.replace(/,/g, '');
          }
        });
      });
    }
  })();
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>