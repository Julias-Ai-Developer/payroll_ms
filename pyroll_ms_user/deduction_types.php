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
    <h2 class="text-2xl font-bold text-slate-800">Existing Deduction Types</h2>
    <div class="flex items-center space-x-3">
      <a href="deductions.php" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm"><i class="fas fa-plus mr-2"></i> Add / Update Types</a>
      <a href="payroll.php" class="text-primary-600 hover:text-primary-800 text-sm">Back to Payroll</a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div id="success-toast" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
    <?php if (count($types) > 0): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold">Name</th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold">Code</th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold">Method</th>
              <th class="py-3 px-4 text-right text-slate-700 font-semibold">Employee</th>
              <th class="py-3 px-4 text-right text-slate-700 font-semibold">Employer</th>
              <th class="py-3 px-4 text-center text-slate-700 font-semibold">Statutory</th>
              <th class="py-3 px-4 text-center text-slate-700 font-semibold">Enabled</th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($types as $t): ?>
              <tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">
                <td class="py-3 px-4"><?php echo htmlspecialchars($t['name']); ?></td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-xs"><?php echo htmlspecialchars($t['code']); ?></span>
                </td>
                <td class="py-3 px-4">
                  <?php 
                    $m = $t['method'];
                    $cls = ($m==='fixed') ? 'bg-sky-100 text-sky-700' : (($m==='percent') ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700');
                    $lbl = ($m==='fixed') ? 'Fixed Amount' : (($m==='percent') ? 'Percentage' : 'Bracket');
                  ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?php echo $cls; ?>"><?php echo $lbl; ?></span>
                </td>
                <td class="py-3 px-4 text-right">
                  <?php 
                    if ($t['method'] === 'fixed') echo 'UGX ' . number_format($t['amount'], 2); 
                    elseif ($t['method'] === 'percent') echo number_format($t['percent'], 2) . '% of gross';
                    else echo 'Bracket-based';
                  ?>
                </td>
                <td class="py-3 px-4 text-right"><?php echo $t['employer_percent'] ? number_format($t['employer_percent'], 2) . '%' : '<span class="text-slate-400">&mdash;</span>'; ?></td>
                <td class="py-3 px-4 text-center">
                  <?php if ($t['statutory']): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700"><i class="fas fa-shield-alt mr-1"></i> Yes</span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">No</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 text-center">
                  <?php if ($t['enabled']): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"><i class="fas fa-check-circle mr-1"></i> Enabled</span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><i class="fas fa-times-circle mr-1"></i> Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 whitespace-nowrap" style="white-space: nowrap;">
                  <div class="flex items-center space-x-2">
                  <button type="button"
                           class="inline-flex items-center bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm mr-2 open-type-modal-btn"
                           data-type-id="<?php echo (int)$t['id']; ?>"
                           data-name="<?php echo htmlspecialchars($t['name'], ENT_QUOTES); ?>"
                           data-method="<?php echo htmlspecialchars($t['method'], ENT_QUOTES); ?>"
                           data-amount="<?php echo (float)$t['amount']; ?>"
                          data-percent="<?php echo (float)$t['percent']; ?>"
                          data-employer-percent="<?php echo (float)$t['employer_percent']; ?>"
                          data-brackets="<?php echo htmlspecialchars($t['brackets'], ENT_QUOTES); ?>"
                          data-description="<?php echo htmlspecialchars($t['description'], ENT_QUOTES); ?>"
                          data-statutory="<?php echo (int)$t['statutory']; ?>"
                          data-enabled="<?php echo (int)$t['enabled']; ?>">
                    <i class="fas fa-pen mr-1"></i> Edit
                   </button>
                  <form method="POST" class="inline-block">
                    <input type="hidden" name="type_id" value="<?php echo (int)$t['id']; ?>" />
                    <input type="hidden" name="new_state" value="<?php echo $t['enabled'] ? 0 : 1; ?>" />
                    <button type="submit" name="toggle_enabled" class="inline-flex items-center <?php echo $t['enabled'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700'; ?> text-white text-xs font-semibold px-3 py-1 rounded shadow-sm">
                      <i class="fas <?php echo $t['enabled'] ? 'fa-toggle-off' : 'fa-toggle-on'; ?> mr-1"></i>
                      <?php echo $t['enabled'] ? 'Disable' : 'Enable'; ?>
                    </button>
                  </form>
                  <form method="POST" class="inline-block">
                    <input type="hidden" name="type_id" value="<?php echo (int)$t['id']; ?>" />
                    <button type="submit" name="delete_type" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm" onclick="return confirm('Delete this deduction type?');">
                      <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                  </form>
                  </div>
                </td>
              </tr>
              
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-slate-500">No deduction types defined yet.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Type Modal -->
<div id="typeEditModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-40 flex items-center justify-center">
  <div class="bg-white rounded-2xl shadow-smooth w-full max-w-2xl p-6 relative">
    <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700" id="closeTypeModalBtn" aria-label="Close">
      <i class="fas fa-times text-lg"></i>
    </button>
    <h3 class="text-xl font-semibold text-slate-800 mb-4">Edit Deduction Type</h3>
    <form method="POST" id="typeEditForm" class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <input type="hidden" name="type_id" />
      <div>
        <label class="block text-gray-700 text-xs font-bold mb-1">Name*</label>
        <input type="text" name="name" class="shadow border rounded w-full py-2 px-3 text-gray-700" required />
      </div>
      <div>
        <label class="block text-gray-700 text-xs font-bold mb-1">Method</label>
        <select name="method" class="shadow border rounded w-full py-2 px-3 text-gray-700">
          <option value="fixed">Fixed Amount</option>
          <option value="percent">Percentage of Gross</option>
          <option value="bracket">Tiered/Bracket-Based</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-700 text-xs font-bold mb-1">Fixed Amount (UGX)</label>
        <input type="text" name="amount" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
      </div>
      <div>
        <label class="block text-gray-700 text-xs font-bold mb-1">Percent (%)</label>
        <input type="text" name="percent" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
      </div>
      <div>
        <label class="block text-gray-700 text-xs font-bold mb-1">Employer Percent (%)</label>
        <input type="text" name="employer_percent" inputmode="decimal" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-gray-700 text-xs font-bold mb-1">Brackets JSON</label>
        <textarea name="brackets" rows="3" class="shadow border rounded w-full py-2 px-3 text-gray-700"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-gray-700 text-xs font-bold mb-1">Description</label>
        <textarea name="description" rows="2" class="shadow border rounded w-full py-2 px-3 text-gray-700"></textarea>
      </div>
      <div class="md:col-span-2 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <label class="inline-flex items-center"><input type="checkbox" name="statutory" class="mr-2" /> Statutory</label>
          <label class="inline-flex items-center"><input type="checkbox" name="enabled" class="mr-2" /> Enabled</label>
        </div>
        <button type="submit" name="update_type" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm"><i class="fas fa-save mr-1"></i> Save</button>
      </div>
    </form>
  </div>
  
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('typeEditModal');
    var closeBtn = document.getElementById('closeTypeModalBtn');
    var form = document.getElementById('typeEditForm');

    function openModal() { modal.classList.remove('hidden'); }
    function closeModal() { modal.classList.add('hidden'); }

    if (closeBtn) {
      closeBtn.addEventListener('click', function () { closeModal(); });
    }
    // Click outside to close
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.open-type-modal-btn').forEach(function(btn) {
      btn.addEventListener('click', function () {
        form.querySelector('input[name="type_id"]').value = btn.dataset.typeId || '';
        form.querySelector('input[name="name"]').value = btn.dataset.name || '';
        form.querySelector('select[name="method"]').value = btn.dataset.method || 'fixed';
        form.querySelector('input[name="amount"]').value = btn.dataset.amount || '';
        form.querySelector('input[name="percent"]').value = btn.dataset.percent || '';
        form.querySelector('input[name="employer_percent"]').value = btn.dataset.employerPercent || '';
        form.querySelector('textarea[name="brackets"]').value = btn.dataset.brackets || '';
        form.querySelector('textarea[name="description"]').value = btn.dataset.description || '';
        form.querySelector('input[name="statutory"]').checked = (btn.dataset.statutory === '1');
        form.querySelector('input[name="enabled"]').checked = (btn.dataset.enabled === '1');
        openModal();
      });
    });

    // Auto-dismiss success toaster after 5 seconds with fade
    var successToast = document.getElementById('success-toast');
    if (successToast) {
      setTimeout(function () {
        successToast.style.transition = 'opacity 300ms ease';
        successToast.style.opacity = '0';
        setTimeout(function () {
          if (successToast && successToast.parentNode) successToast.parentNode.removeChild(successToast);
        }, 320);
      }, 5000);
    }
  });
</script>

<?php
$page_content = ob_get_clean();
include 'includes/layout.php';
?>