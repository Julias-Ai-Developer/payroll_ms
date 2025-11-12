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

// Fetch owner details to verify existence
$sql = "SELECT id, full_name, username FROM business_owners WHERE id = ?";
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

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Optional: Check if owner has associated businesses or employees
        $check_sql = "SELECT COUNT(*) as count FROM businesses WHERE id IN (SELECT business_id FROM business_owners WHERE id = ?)";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $owner_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        // Delete the owner
        $delete_sql = "DELETE FROM business_owners WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $owner_id);
        
        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect with success message
            header('Location: owners.php?success=' . urlencode('Business owner deleted successfully'));
            exit();
        } else {
            throw new Exception("Failed to delete owner");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header('Location: owners.php?error=' . urlencode('Failed to delete owner: ' . $e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Owner - Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'roboto': ['Roboto', 'sans-serif'],
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
                        <h1 class="text-2xl font-bold text-gray-800">Delete Business Owner</h1>
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
            <main class="flex-1 overflow-y-auto bg-gray-50 flex items-center justify-center p-6">
                <div class="max-w-2xl w-full">
                    <!-- Warning Card -->
                    <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-5">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                                </div>
                                <div class="text-white">
                                    <h2 class="text-2xl font-bold">Delete Confirmation</h2>
                                    <p class="text-red-100 text-sm">This action cannot be undone</p>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <!-- Warning Message -->
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                                <div class="flex">
                                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                                    <div>
                                        <h3 class="text-lg font-bold text-red-800 mb-2">Warning: Permanent Deletion</h3>
                                        <p class="text-red-700 text-sm">
                                            You are about to permanently delete this business owner account. This action will:
                                        </p>
                                        <ul class="list-disc list-inside mt-2 text-sm text-red-700 space-y-1">
                                            <li>Remove all owner information from the system</li>
                                            <li>Revoke access to the owner dashboard</li>
                                            <li>May affect associated business data</li>
                                            <li><strong>Cannot be reversed or recovered</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Owner Details -->
                            <div class="bg-gray-50 rounded-lg p-5 mb-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user-tie text-gray-600 mr-2"></i>
                                    Owner to be Deleted
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200">
                                        <span class="text-sm font-medium text-gray-600">Owner ID:</span>
                                        <span class="text-sm font-bold text-gray-900">#<?php echo $owner['id']; ?></span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200">
                                        <span class="text-sm font-medium text-gray-600">Full Name:</span>
                                        <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($owner['full_name']); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-sm font-medium text-gray-600">Username:</span>
                                        <span class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($owner['username']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmation Question -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <p class="text-center text-gray-700 font-medium">
                                    <i class="fas fa-question-circle text-yellow-600 mr-2"></i>
                                    Are you absolutely sure you want to delete this business owner?
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="confirm_delete" value="1">
                                
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button type="submit" 
                                            class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg hover:shadow-xl font-medium"
                                            onclick="return confirm('FINAL WARNING: This will permanently delete the owner. Are you absolutely sure?');">
                                        <i class="fas fa-trash-alt mr-2"></i>
                                        Yes, Delete Permanently
                                    </button>
                                    
                                    <a href="owners.php" 
                                       class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                                        <i class="fas fa-times mr-2"></i>
                                        Cancel, Keep Owner
                                    </a>
                                </div>
                                
                                <div class="text-center">
                                    <a href="owner_view.php?id=<?php echo $owner_id; ?>" 
                                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                        <i class="fas fa-eye mr-1"></i>
                                        View owner details before deciding
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Footer Info -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <p class="text-xs text-gray-600 text-center">
                                <i class="fas fa-shield-alt mr-1"></i>
                                This action is logged in the system audit trail
                            </p>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 text-lg mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="font-medium text-blue-900 mb-1">Alternative Options</h4>
                                <p class="text-sm text-blue-800">
                                    Consider deactivating the account instead of deleting if you might need the data later. 
                                    You can edit the owner and set their status to "Inactive".
                                </p>
                                <a href="owner_edit.php?id=<?php echo $owner_id; ?>" 
                                   class="inline-flex items-center mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit owner instead
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