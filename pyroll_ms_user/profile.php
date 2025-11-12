<?php
session_start();
require_once 'config/database.php';

// Redirect to login if session values are missing
if (!isset($_SESSION['business_id']) || !isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit();
}

$username        = $_SESSION['username'];
$full_name       = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $username;
$business_name   = isset($_SESSION['business_name']) ? $_SESSION['business_name'] : '';
$business_addr   = isset($_SESSION['business_address']) ? $_SESSION['business_address'] : '';
$business_role   = isset($_SESSION['business_role']) ? $_SESSION['business_role'] : '';

// Start output buffering
ob_start();
?>

<div class="mb-6">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Profile</h2>
    <a href="dashboard.php" class="text-primary-600 hover:text-primary-800 text-sm">Back to Dashboard</a>
  </div>

  <!-- User Profile Card -->
  <div class="bg-white rounded-2xl shadow-smooth p-6 border border-slate-200 mb-6">
    <div class="flex items-start space-x-4">
      <div class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
        <?php echo strtoupper(substr($username, 0, 1)); ?>
      </div>
      <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-slate-500 text-xs">Full Name</p>
          <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($full_name); ?></p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Username</p>
          <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($username); ?></p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Role</p>
          <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($business_role); ?></p>
        </div>
        <div>
          <p class="text-slate-500 text-xs">Business</p>
          <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($business_name); ?></p>
        </div>
        <div class="md:col-span-2">
          <p class="text-slate-500 text-xs">Address</p>
          <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($business_addr); ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Account Actions -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
      <h3 class="text-lg font-semibold text-slate-800 mb-3">Security</h3>
      <p class="text-slate-600 text-sm mb-4">Manage your account security settings.</p>
      <div class="flex items-center gap-3">
        <a href="auth/forgot_password.php" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-md hover:shadow-lg">
          <i class="fas fa-key mr-2"></i>
          Reset Password
        </a>
        <a href="auth/logout.php" class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100">
          <i class="fas fa-sign-out-alt mr-2"></i>
          Logout
        </a>
      </div>
    </div>

    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
      <h3 class="text-lg font-semibold text-slate-800 mb-3">Business</h3>
      <p class="text-slate-600 text-sm mb-4">Review business details saved to your session.</p>
      <ul class="text-slate-700 text-sm space-y-2">
        <li><span class="text-slate-500">ID:</span> <?php echo (int)$_SESSION['business_id']; ?></li>
        <li><span class="text-slate-500">Name:</span> <?php echo htmlspecialchars($business_name); ?></li>
        <li><span class="text-slate-500">Address:</span> <?php echo htmlspecialchars($business_addr); ?></li>
      </ul>
    </div>
  </div>
</div>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the layout
include 'includes/layout.php';
?>