<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: business.php?error=Invalid business ID");
    exit;
}

$business_id = $_GET['id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'deactivate';

// Get business details
$sql = "SELECT * FROM businesses WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $business = $result->fetch_assoc();
    } else {
        header("Location: business.php?error=Business not found");
        exit;
    }
    
    $stmt->close();
} else {
    header("Location: business.php?error=Database error");
    exit;
}

// Process deletion or deactivation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        if ($action == 'delete') {
            // Check if there are owners linked to this business
            $check_owners = "SELECT COUNT(*) as count FROM business_owners WHERE business_id = ?";
            if ($check_stmt = $conn->prepare($check_owners)) {
                $check_stmt->bind_param("i", $business_id);
                $check_stmt->execute();
                $owner_result = $check_stmt->get_result();
                $owner_count = $owner_result->fetch_assoc()['count'];
                $check_stmt->close();
                
                if ($owner_count > 0) {
                    header("Location: business.php?error=Cannot delete business with linked owners. Remove owners first or deactivate instead.");
                    exit;
                }
                
                // Check if there are employees linked to this business
                $check_employees = "SELECT COUNT(*) as count FROM employees WHERE business_id = ?";
                if ($check_emp_stmt = $conn->prepare($check_employees)) {
                    $check_emp_stmt->bind_param("i", $business_id);
                    $check_emp_stmt->execute();
                    $emp_result = $check_emp_stmt->get_result();
                    $emp_count = $emp_result->fetch_assoc()['count'];
                    $check_emp_stmt->close();
                    
                    if ($emp_count > 0) {
                        header("Location: business.php?error=Cannot delete business with linked employees. Remove employees first or deactivate instead.");
                        exit;
                    }
                    
                    // Delete the business
                    $delete_sql = "DELETE FROM businesses WHERE id = ?";
                    if ($delete_stmt = $conn->prepare($delete_sql)) {
                        $delete_stmt->bind_param("i", $business_id);
                        
                        if ($delete_stmt->execute()) {
                            // Log the action
                            logAdminAction($conn, "Business Deletion", "Deleted business: {$business['business_name']}");
                            
                            // Redirect to business list with success message
                            header("Location: business.php?success=Business deleted successfully");
                            exit;
                        } else {
                            header("Location: business.php?error=Error deleting business");
                            exit;
                        }
                        
                        $delete_stmt->close();
                    }
                }
            }
        } else {
            // Deactivate the business
            $status = 'inactive';
            $update_sql = "UPDATE businesses SET status = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("si", $status, $business_id);
                
                if ($update_stmt->execute()) {
                    // Log the action
                    logAdminAction($conn, "Business Deactivation", "Deactivated business: {$business['business_name']}");
                    
                    // Redirect to business list with success message
                    header("Location: business.php?success=Business deactivated successfully");
                    exit;
                } else {
                    header("Location: business.php?error=Error deactivating business");
                    exit;
                }
                
                $update_stmt->close();
            }
        }
    } else {
        // User canceled the action
        header("Location: business.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($action); ?> Business - Payroll Management System</title>
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
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #0284c7;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0369a1;
        }
        
        /* Custom gradient background for sidebar */
        .bg-blue-gradient {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        }
        
        /* Pulse animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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
                        <a href="business.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg shadow-lg backdrop-blur-sm">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                            <span class="font-medium">Businesses</span>
                        </a>
                    </li>
                    <li>
                        <a href="owners.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
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
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <!-- Header Section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-<?php echo ($action == 'delete') ? 'trash-alt' : 'ban'; ?> text-red-600 mr-3"></i>
                                <?php echo ucfirst($action); ?> Business
                            </h2>
                            <nav class="text-sm mt-2">
                                <ol class="flex list-none p-0">
                                    <li class="flex items-center">
                                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 hover:underline">Dashboard</a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    </li>
                                    <li class="flex items-center">
                                        <a href="business.php" class="text-blue-600 hover:text-blue-800 hover:underline">Businesses</a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    </li>
                                    <li class="text-gray-600 font-medium"><?php echo ucfirst($action); ?> Business</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="business.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                    </div>
                </div>
                
                <!-- Warning Alert -->
                <div class="bg-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-50 border-l-4 border-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-500 p-6 mb-6 rounded-r-lg shadow-md">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-600 text-3xl animate-pulse-slow"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-900 mb-2">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Warning: Confirm <?php echo ucfirst($action); ?>
                            </h3>
                            <?php if ($action == 'delete'): ?>
                                <p class="text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-800 mb-2">
                                    You are about to <strong>permanently delete</strong> the business 
                                    "<strong><?php echo htmlspecialchars($business['business_name']); ?></strong>".
                                </p>
                                <p class="text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    This action <strong>cannot be undone</strong>. All data related to this business will be permanently removed from the system.
                                </p>
                            <?php else: ?>
                                <p class="text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-800 mb-2">
                                    You are about to <strong>deactivate</strong> the business 
                                    "<strong><?php echo htmlspecialchars($business['business_name']); ?></strong>".
                                </p>
                                <p class="text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    The business will be marked as inactive but all data will be preserved. You can reactivate it later from the edit page.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Business Details Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                        <h5 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Business Details
                        </h5>
                        <p class="text-gray-300 text-sm mt-1">Review the information before confirming</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-building text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Business Name</p>
                                        <p class="text-sm text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($business['business_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-id-card text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Registration Number</p>
                                        <p class="text-sm text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($business['registration_number']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-briefcase text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Business Type</p>
                                        <p class="text-sm text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($business['business_type']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="far fa-calendar-alt text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Registration Date</p>
                                        <p class="text-sm text-gray-900 font-medium mt-1">
                                            <?php echo date('F d, Y', strtotime($business['registration_date'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-toggle-on text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Current Status</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo ($business['status'] == 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> mt-1">
                                            <span class="w-2 h-2 mr-1.5 rounded-full <?php echo ($business['status'] == 'active') ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                            <?php echo ucfirst(htmlspecialchars($business['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-envelope text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Contact Email</p>
                                        <p class="text-sm text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($business['email']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Confirmation Form -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-600 to-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-700 px-6 py-4">
                        <h5 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Confirm Action
                        </h5>
                        <p class="text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-100 text-sm mt-1">Please confirm before proceeding</p>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $business_id . "&action=" . $action); ?>" method="post" class="p-6">
                        <div class="mb-6">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" 
                                           id="confirm" 
                                           name="confirm" 
                                           value="yes" 
                                           class="w-6 h-6 text-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-600 border-gray-300 rounded focus:ring-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-500"
                                           required>
                                </div>
                                <span class="text-gray-700 font-medium group-hover:text-gray-900 transition-colors">
                                    I confirm that I want to <strong><?php echo $action; ?></strong> this business and understand the consequences
                                </span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="business.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-600 to-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-700 text-white rounded-lg hover:from-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-700 hover:to-<?php echo ($action == 'delete') ? 'red' : 'yellow'; ?>-800 transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                                <i class="fas fa-<?php echo ($action == 'delete') ? 'trash-alt' : 'ban'; ?> mr-2"></i>
                                <?php echo ucfirst($action); ?> Business
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Additional Info -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-lightbulb text-blue-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <h6 class="font-semibold text-blue-900 mb-1">Important Information</h6>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <?php if ($action == 'delete'): ?>
                                    <li>• Deletion is only possible if there are no linked owners or employees</li>
                                    <li>• If deletion fails, consider deactivating the business instead</li>
                                    <li>• This action is logged in the audit trail for security purposes</li>
                                <?php else: ?>
                                    <li>• Deactivated businesses can be reactivated from the edit page</li>
                                    <li>• All business data remains intact when deactivated</li>
                                    <li>• Linked owners and employees are not affected by deactivation</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission with loading state
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const checkbox = document.getElementById('confirm');
                if (!checkbox.checked) {
                    e.preventDefault();
                    alert('Please confirm the action by checking the checkbox.');
                    return false;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            });
        });
    </script>
</body>
</html>