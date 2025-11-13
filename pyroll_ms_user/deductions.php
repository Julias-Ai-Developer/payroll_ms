<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['business_id'])) {
    header('Location: auth/login.php');
    exit();
}

$business_id = (int) $_SESSION['business_id'];
$error = $success = "";

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_type'])) {
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $method = $_POST['method'] ?? 'fixed';
        $amount = floatval($_POST['amount'] ?? 0);
        $percent = floatval($_POST['percent'] ?? 0);
        $employer_percent = floatval($_POST['employer_percent'] ?? 0);
        $brackets = trim($_POST['brackets'] ?? '');
        $statutory = isset($_POST['statutory']) ? 1 : 0;
        $enabled = isset($_POST['enabled']) ? 1 : 1;
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $code === '') {
            $error = 'Name and code are required.';
        } else if ($method === 'bracket' && $brackets !== '') {
            $dec = json_decode($brackets, true);
            if (!is_array($dec)) {
                $error = 'Invalid brackets JSON format.';
            }
        }

        if ($error === '') {
            // Upsert by code + business
            $exists = $conn->prepare("SELECT id FROM deduction_types WHERE code = ? AND business_id = ?");
            $exists->bind_param("si", $code, $business_id);
            $exists->execute();
            $res = $exists->get_result();
            $exists->close();

            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $id = (int)$row['id'];
                $upd = $conn->prepare("UPDATE deduction_types SET name=?, method=?, amount=?, percent=?, employer_percent=?, brackets=?, statutory=?, enabled=?, description=? WHERE id=?");
                $upd->bind_param("ssdddsiisi", $name, $method, $amount, $percent, $employer_percent, $brackets, $statutory, $enabled, $description, $id);
                if ($upd->execute()) {
                    $success = 'Deduction type updated.';
                } else {
                    $error = 'Failed to update deduction type: ' . $conn->error;
                }
                $upd->close();
            } else {
                $ins = $conn->prepare("INSERT INTO deduction_types (business_id, name, code, method, amount, percent, employer_percent, brackets, statutory, enabled, description) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $ins->bind_param("isssdddsiis", $business_id, $name, $code, $method, $amount, $percent, $employer_percent, $brackets, $statutory, $enabled, $description);
                if ($ins->execute()) {
                    $success = 'Deduction type created.';
                } else {
                    $error = 'Failed to create deduction type: ' . $conn->error;
                }
                $ins->close();
            }
        }
    }

    if (isset($_POST['update_type'])) {
        $type_id = (int)($_POST['type_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $method = $_POST['method'] ?? 'fixed';
        $amount = floatval($_POST['amount'] ?? 0);
        $percent = floatval($_POST['percent'] ?? 0);
        $employer_percent = floatval($_POST['employer_percent'] ?? 0);
        $brackets = trim($_POST['brackets'] ?? '');
        $statutory = isset($_POST['statutory']) ? 1 : 0;
        $enabled = isset($_POST['enabled']) ? 1 : 1;
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $error = 'Name is required.';
        } else if ($method === 'bracket' && $brackets !== '') {
            $dec = json_decode($brackets, true);
            if (!is_array($dec)) {
                $error = 'Invalid brackets JSON format.';
            }
        }

        if ($error === '') {
            $upd = $conn->prepare("UPDATE deduction_types SET name=?, method=?, amount=?, percent=?, employer_percent=?, brackets=?, statutory=?, enabled=?, description=? WHERE id=? AND business_id=?");
            $upd->bind_param("ssdddsiisii", $name, $method, $amount, $percent, $employer_percent, $brackets, $statutory, $enabled, $description, $type_id, $business_id);
            if ($upd->execute()) {
                $success = 'Deduction type updated.';
            } else {
                $error = 'Failed to update deduction type: ' . $conn->error;
            }
            $upd->close();
        }
    }

    if (isset($_POST['toggle_enabled'])) {
        $type_id = (int)($_POST['type_id'] ?? 0);
        $new_state = (int)($_POST['new_state'] ?? 1);
        $upd = $conn->prepare("UPDATE deduction_types SET enabled = ? WHERE id = ? AND business_id = ?");
        $upd->bind_param("iii", $new_state, $type_id, $business_id);
        if ($upd->execute()) {
            $success = 'Deduction type status updated.';
        } else {
            $error = 'Failed to update status: ' . $conn->error;
        }
        $upd->close();
    }

    if (isset($_POST['delete_type'])) {
        $type_id = (int)($_POST['type_id'] ?? 0);
        $chk = $conn->prepare("SELECT statutory FROM deduction_types WHERE id = ? AND business_id = ?");
        $chk->bind_param("ii", $type_id, $business_id);
        $chk->execute();
        $res = $chk->get_result();
        $chk->close();
        if (!$res || $res->num_rows === 0) {
            $error = 'Deduction type not found.';
        } else {
            $row = $res->fetch_assoc();
            if ((int)$row['statutory'] === 1) {
                $error = 'Cannot delete statutory deduction type.';
            }
        }

        if ($error === '') {
            $ref = $conn->prepare("SELECT id FROM employee_deductions WHERE deduction_id = ? LIMIT 1");
            $ref->bind_param("i", $type_id);
            $ref->execute();
            $refRes = $ref->get_result();
            $ref->close();
            if ($refRes && $refRes->num_rows > 0) {
                $error = 'Cannot delete: deduction is assigned to employees.';
            }
        }

        if ($error === '') {
            $del = $conn->prepare("DELETE FROM deduction_types WHERE id = ? AND business_id = ?");
            $del->bind_param("ii", $type_id, $business_id);
            if ($del->execute()) {
                $success = 'Deduction type deleted.';
            } else {
                $error = 'Failed to delete deduction type: ' . $conn->error;
            }
            $del->close();
        }
    }
}

// Fetch all types for this business
$types = [];
$stmt = $conn->prepare("SELECT * FROM deduction_types WHERE business_id = ? ORDER BY statutory DESC, name ASC");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $types[] = $row; }
$stmt->close();

// Start output buffering
ob_start();
?>

<div class="mb-6">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Deductions Management</h2>
    <div class="flex items-center space-x-3">
      <a href="deduction_types.php" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm"><i class="fas fa-list mr-2"></i> Existing Types</a>
      <a href="payroll.php" class="text-primary-600 hover:text-primary-800 text-sm">Back to Payroll</a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Create / Update Deduction Type -->
    <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
      <h3 class="text-xl font-semibold text-slate-800 mb-4">Add / Update Deduction Type</h3>
      <form method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-600">Name*</label>
            <input type="text" name="name" class="mt-1 w-full border rounded px-3 py-2" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600">Code*</label>
            <input type="text" name="code" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., PAYE, NSSF, LST, LOAN" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600">Method</label>
            <select name="method" class="mt-1 w-full border rounded px-3 py-2">
              <option value="fixed">Fixed Amount</option>
              <option value="percent">Percentage of Gross</option>
              <option value="bracket">Tiered/Bracket-Based</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600">Fixed Amount (UGX)</label>
            <input type="text" name="amount" inputmode="decimal" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., 50000" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600">Percent (%)</label>
            <input type="text" name="percent" inputmode="decimal" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., 10" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600">Employer Percent (%)</label>
            <input type="text" name="employer_percent" inputmode="decimal" class="mt-1 w-full border rounded px-3 py-2" placeholder="NSSF employer share" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-600">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full border rounded px-3 py-2" placeholder="Notes or policy reference"></textarea>
          </div>
        </div>
        <div class="flex items-center justify-between mt-4">
          <div class="flex items-center space-x-4">
            <label class="inline-flex items-center"><input type="checkbox" name="statutory" class="mr-2" /> Statutory (auto-applied)</label>
            <label class="inline-flex items-center"><input type="checkbox" name="enabled" checked class="mr-2" /> Enabled</label>
          </div>
          <button type="submit" name="save_type" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-4 py-2 rounded-lg">Save Type</button>
        </div>
      </form>
    </div>

    <!-- Existing Types redirect -->
    <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
      <h3 class="text-xl font-semibold text-slate-800 mb-4">Existing Deduction Types</h3>
      <p class="text-slate-600 mb-4">Manage and edit deduction types on a dedicated page.</p>
      <a href="deduction_types.php" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm"><i class="fas fa-list mr-2"></i> Go to Existing Types</a>
    </div>
  </div>
</div>

<?php
$page_content = ob_get_clean();
include 'includes/layout.php';
?>