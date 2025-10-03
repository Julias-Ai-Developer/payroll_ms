<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

// Get owner ID from URL
$owner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($owner_id <= 0) {
    header('Location: owners.php?error=' . urlencode('Invalid owner ID'));
    exit();
}

// Fetch owner details with business information
$sql = "SELECT bo.*, b.business_name, b.business_type, b.registration_number 
        FROM business_owners bo
        LEFT JOIN businesses b ON bo.business_id = b.id
        WHERE bo.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: owners.php?error=' . urlencode('Owner not found'));
    exit();
}

$owner = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Owner - <?php echo htmlspecialchars($owner['full_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'roboto': ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        .bg-blue-gradient {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #0284c7;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0369a1;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-roboto antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-gradient text-white shadow-2xl flex-shrink-0">
            <div class="p-6 border-b border-blue-600">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-briefcase text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold">Admin Panel</h4>
                        <p class="text-xs text-blue-100">Payroll Management</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-6 px-3">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-tachometer-alt text-lg"></i>
                            </div>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="business.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                            <span class="font-medium">Businesses</span>
                        </a>
                    </li>
                    <li>
                        <a href="owners.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg shadow-lg backdrop-blur-sm">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-tie text-lg"></i>
                            </div>
                            <span class="font-medium">Business Owners</span>
                        </a>
                    </li>
                    <li>
                        <a href="audit_logs.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-lg"></i>
                            </div>
                            <span class="font-medium">Audit Logs</span>
                        </a>
                    </li>
                </ul>
                
                <div class="mt-8 pt-6 border-t border-blue-600">
                    <a href="../logout.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-red-500/20 hover:text-white">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </div>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-md z-10">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">View Business Owner</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i>
                            <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Welcome back!</p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                            </div>
                        </div>
                        <a href="../logout.php" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-6 space-y-6">
                    <!-- Page Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-user-tie text-blue-600 mr-3"></i>
                                Business Owner Details
                            </h2>
                            <nav class="text-sm mt-2">
                                <ol class="flex list-none p-0">
                                    <li class="flex items-center">
                                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 hover:underline">Dashboard</a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    </li>
                                    <li class="flex items-center">
                                        <a href="owners.php" class="text-blue-600 hover:text-blue-800 hover:underline">Business Owners</a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    </li>
                                    <li class="text-gray-600 font-medium">View Details</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="owners.php" class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-200 shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                    </div>

                    <!-- Owner Details Card -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-blue-gradient px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-tie text-3xl text-blue-600"></i>
                                    </div>
                                    <div class="text-white">
                                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($owner['full_name']); ?></h2>
                                        <p class="text-blue-100 text-sm">Owner ID: #<?php echo $owner['id']; ?></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold <?php echo ($owner['status'] == 'active') ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                                    <span class="w-2 h-2 mr-2 rounded-full <?php echo ($owner['status'] == 'active') ? 'bg-white' : 'bg-white'; ?>"></span>
                                    <?php echo ucfirst(htmlspecialchars($owner['status'] ?? 'active')); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6">
                            <!-- Personal Information -->
                            <div class="mb-8">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                                    Personal Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-user mr-2 text-gray-400"></i>Full Name
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['full_name']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-envelope mr-2 text-gray-400"></i>Email Address
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['email']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-phone mr-2 text-gray-400"></i>Phone Number
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['phone']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-briefcase mr-2 text-gray-400"></i>Business Role
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['business_role']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div class="mb-8">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-building text-blue-600 mr-2"></i>
                                    Business Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-store mr-2 text-gray-400"></i>Business Name
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['business_name'] ?? 'Not Assigned'); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-tag mr-2 text-gray-400"></i>Business Type
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['business_type'] ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-id-card mr-2 text-gray-400"></i>Registration Number
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['registration_number'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    Account Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-user-tag mr-2 text-gray-400"></i>Username
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($owner['username']); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-toggle-on mr-2 text-gray-400"></i>Account Status
                                        </label>
                                        <p class="text-gray-900 font-medium">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo ($owner['status'] == 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($owner['status'] ?? 'active')); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>Registration Date
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo date('F j, Y, g:i a', strtotime($owner['created_at'])); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <label class="text-sm font-semibold text-gray-600 block mb-1">
                                            <i class="fas fa-edit mr-2 text-gray-400"></i>Last Updated
                                        </label>
                                        <p class="text-gray-900 font-medium"><?php echo date('F j, Y, g:i a', strtotime($owner['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">
                                <a href="owner_edit.php?id=<?php echo $owner['id']; ?>" class="inline-flex items-center px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 shadow-lg">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Owner
                                </a>
                                <a href="owner_reset_password.php?id=<?php echo $owner['id']; ?>" class="inline-flex items-center px-5 py-2.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-all duration-200 shadow-lg">
                                    <i class="fas fa-key mr-2"></i>
                                    Reset Password
                                </a>
                                <a href="owner_delete.php?id=<?php echo $owner['id']; ?>" class="inline-flex items-center px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 shadow-lg" onclick="return confirm('Are you sure you want to delete this owner?');">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Owner
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>