<?php
session_start();
$business_id = $_SESSION['business_id'];
require_once 'config/database.php';

// Initialize variables
$name = $position = $employee_id = "";
$basic_salary = $allowances = 0;
$error = $success = "";
$edit_id = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getConnection();

    // For employee creation or update
    if (isset($_POST['save_employee'])) {
        // Get form data
        $employee_id = trim($_POST['employee_id']);
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $basic_salary = floatval($_POST['basic_salary']);
        $allowances = floatval($_POST['allowances']);

        // Validate input
        if (empty($employee_id) || empty($name) || empty($position) || $basic_salary <= 0) {
            $error = "Please fill all required fields with valid data";
        } else {
            // Check if we're updating or creating
            if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
                // Update existing employee
                $id = $_POST['edit_id'];
                
                // Check if employee ID is being changed to one that already exists (excluding current employee)
                $check_sql = "SELECT id FROM employees WHERE employee_id = ? AND id != ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("si", $employee_id, $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error = "Employee ID already exists. Please use a different ID.";
                } else {
                    // Update existing employee
                    $sql = "UPDATE employees SET employee_id = ?, name = ?, position = ?, basic_salary = ?, allowances = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssddi", $employee_id, $name, $position, $basic_salary, $allowances, $id);

                    if ($stmt->execute()) {
                        $success = "Employee updated successfully";
                        // Reset form fields
                        $name = $position = $employee_id = "";
                        $basic_salary = $allowances = 0;
                        $edit_id = null;
                    } else {
                        $error = "Error updating employee: " . $conn->error;
                    }
                }
            } else {
                // Check if employee ID already exists for new employee
                $check_sql = "SELECT id FROM employees WHERE employee_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $employee_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error = "Employee ID already exists. Please use a different ID.";
                } else {
                    // Create new employee
                    $sql = "INSERT INTO employees (employee_id, name, position, business_id, basic_salary, allowances) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssidd", $employee_id, $name, $position, $business_id, $basic_salary, $allowances);

                    if ($stmt->execute()) {
                        $success = "Employee added successfully";
                        // Reset form fields
                        $name = $position = $employee_id = "";
                        $basic_salary = $allowances = 0;
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

    $conn->close();
}

// Start output buffering
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Employee Management</h2>

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
                    <input type="text" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($employee_id); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
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
                    <input type="number" id="basic_salary" name="basic_salary" value="<?php echo $basic_salary; ?>" min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="8900000000" required>
                </div>

                <div>
                    <label for="allowances" class="block text-gray-700 text-sm font-bold mb-2">Allowances</label>
                    <input type="number" id="allowances" name="allowances" placeholder="50000" value="<?php echo $allowances; ?>" min="0" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

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
            echo '<table class="min-w-full bg-white">';
            echo '<thead class="bg-gray-100">';
            echo '<tr>';
            echo '<th class="py-2 px-4 text-left text-gray-600">ID</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Name</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Position</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Basic Salary</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Allowances</th>';
            echo '<th class="py-2 px-4 text-left text-gray-600">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr class="border-b hover:bg-gray-50">';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['employee_id']) . '</td>';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['name']) . '</td>';
                echo '<td class="py-2 px-4">' . htmlspecialchars($row['position']) . '</td>';
                echo '<td class="py-2 px-4">Ugx ' . number_format($row['basic_salary'], 2) . '</td>';
                echo '<td class="py-2 px-4">Ugx ' . number_format($row['allowances'], 2) . '</td>';
                echo '<td class="py-2 px-4">';

                // Edit form
                echo '<form method="post" class="inline mr-1">';
                echo '<input type="hidden" name="edit_id" value="' . $row['id'] . '">';
                echo '<button type="submit" name="edit_employee" class="text-blue-500 hover:text-blue-700">';
                echo '<i class="fas fa-edit"></i>';
                echo '</button>';
                echo '</form>';

                // Delete form
                echo '<form method="post" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this employee?\')">';
                echo '<input type="hidden" name="delete_id" value="' . $row['id'] . '">';
                echo '<button type="submit" name="delete_employee" class="text-red-500 hover:text-red-700">';
                echo '<i class="fas fa-trash"></i>';
                echo '</button>';
                echo '</form>';

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

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>