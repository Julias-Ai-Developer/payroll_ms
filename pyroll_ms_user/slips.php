<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['business_id'])) {
    header('Location: auth/login.php');
    exit();
}

$business_id = (int) $_SESSION['business_id'];

// Fetch recent payroll records for this business
$conn = getConnection();
$sql = "SELECT p.id, p.month, p.year, p.net_salary, p.created_at, e.name AS employee_name, e.employee_id AS emp_id
        FROM payroll p
        JOIN employees e ON p.employee_id = e.id
        WHERE p.business_id = ?
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $business_id);
$stmt->execute();
$result = $stmt->get_result();

// Start output buffering
ob_start();
?>

<div class="mb-6">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Salary Slips</h2>
    <a href="payroll.php" class="text-primary-600 hover:text-primary-800 text-sm">Go to Payroll</a>
  </div>

  <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200">
    <?php if ($result->num_rows > 0): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm sortable-table">
          <thead class="bg-slate-50">
            <tr>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Employee <i class="fas fa-sort ml-1 text-slate-400"></i></th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="string">Employee ID <i class="fas fa-sort ml-1 text-slate-400"></i></th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="date">Period <i class="fas fa-sort ml-1 text-slate-400"></i></th>
              <th class="py-3 px-4 text-right text-slate-700 font-semibold cursor-pointer sortable-th" data-type="number">Net Salary <i class="fas fa-sort ml-1 text-slate-400"></i></th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold cursor-pointer sortable-th" data-type="date">Created <i class="fas fa-sort ml-1 text-slate-400"></i></th>
              <th class="py-3 px-4 text-left text-slate-700 font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">
                <td class="py-3 px-4 font-medium text-slate-800" data-sort-value="<?= htmlspecialchars($row['employee_name']) ?>"><?= htmlspecialchars($row['employee_name']) ?></td>
                <td class="py-3 px-4" data-sort-value="<?= htmlspecialchars($row['emp_id']) ?>"><span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-xs"><?= htmlspecialchars($row['emp_id']) ?></span></td>
                <td class="py-3 px-4 text-slate-700" data-sort-value="<?= strtotime($row['month'] . ' 1, ' . (int)$row['year']) ?>"><?= htmlspecialchars($row['month']) . ' ' . (int)$row['year'] ?></td>
                <td class="py-3 px-4 text-right text-slate-800" data-sort-value="<?= (float)$row['net_salary'] ?>">UGX <?= number_format($row['net_salary'], 2) ?></td>
                <td class="py-3 px-4 text-slate-700" data-sort-value="<?= strtotime($row['created_at']) ?>"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                <td class="py-3 px-4 whitespace-nowrap" style="white-space: nowrap;">
                  <div class="flex items-center space-x-2">
                    <a href="salary_slip.php?id=<?= (int)$row['id'] ?>" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-1 rounded shadow-sm">
                      <i class="fas fa-file-invoice mr-1"></i> View Slip
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-slate-600">No salary slips found. Process payroll to generate slips.</p>
    <?php endif; ?>
  </div>
</div>

<?php
$stmt->close();
$conn->close();

// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>