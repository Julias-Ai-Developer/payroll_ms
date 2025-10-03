<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

$success = $error = '';

// Get all active businesses for dropdown
$businesses_sql = "SELECT id, business_name FROM businesses WHERE status = 'active' ORDER BY business_name";
$businesses_result = $conn->query($businesses_sql);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = trim($_POST['full_name']);
    $id_number = trim($_POST['id_number']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $business_id = trim($_POST['business_id']);
    $business_role = trim($_POST['business_role']);
    
    // Validate input
    if (empty($full_name) || empty($id_number) || empty($email) || 
        empty($phone) || empty($address) || empty($business_id) || empty($business_role)) {
        $error = "Please fill all required fields";
    } else {
        // Check if owner with this email already exists
        $check_sql = "SELECT id FROM business_owners WHERE email = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "An owner with this email already exists";
            } else {
                // Generate username and temporary password
                $username = strtolower(explode('@', $email)[0]) . rand(100, 999);
                $temp_password = generateRandomPassword(10);
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
                // Insert new owner
                $insert_sql = "INSERT INTO business_owners (full_name, id_number, email, phone, address, 
                                business_id, business_role, username, password, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
                
                if ($insert_stmt = $conn->prepare($insert_sql)) {
                    $insert_stmt->bind_param("sssssssss", $full_name, $id_number, $email, $phone, 
                                           $address, $business_id, $business_role, $username, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $owner_id = $insert_stmt->insert_id;
                        
                        // Log the action
                        logAdminAction($conn, "Owner Registration", "Registered new owner: $full_name");
                        
                        // Set success message with credentials
                        $success = "Owner registered successfully!||Username: $username||Password: $temp_password";
                    } else {
                        $error = "Error: " . $insert_stmt->error;
                    }
                    
                    $insert_stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
            
            $check_stmt->close();
        } else {
            $error = "Error checking owner: " . $conn->error;
        }
    }
}

// Function to generate random password
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    
    return $password;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Business Owner - Payroll Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'roboto': ['"Roboto Condensed"', 'sans-serif']
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
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
        
        /* Custom scrollbar */
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
        
        /* Custom gradient background for sidebar */
        .bg-blue-gradient {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        }
        
        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.3s ease;
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
                        <h1 class="text-2xl font-bold text-gray-800">Register Business Owner</h1>
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
                                <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                                Register New Owner
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
                                    <li class="text-gray-600 font-medium">Register Owner</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    
                    <!-- Alert Messages -->
                    <?php if (!empty($success)): 
                        $parts = explode('||', $success);
                        $message = $parts[0];
                        $username = isset($parts[1]) ? str_replace('Username: ', '', $parts[1]) : '';
                        $password = isset($parts[2]) ? str_replace('Password: ', '', $parts[2]) : '';
                    ?>
                        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle mr-3 text-green-500 text-xl mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="font-semibold"><?php echo $message; ?></p>
                                    <?php if ($username && $password): ?>
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="font-bold text-blue-900 mb-2">Login Credentials:</p>
                                            <div class="space-y-1 text-sm">
                                                <p><span class="font-semibold">Username:</span> <code class="bg-white px-2 py-1 rounded"><?php echo $username; ?></code></p>
                                                <p><span class="font-semibold">Temporary Password:</span> <code class="bg-white px-2 py-1 rounded"><?php echo $password; ?></code></p>
                                            </div>
                                            <p class="mt-2 text-xs text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Please save or share these credentials securely with the business owner.
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-red-500 text-xl"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Registration Form -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-6 py-4">
                            <h5 class="text-lg font-bold text-gray-800 flex items-center">
                                <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                                Owner Information
                            </h5>
                        </div>
                        <div class="p-6">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Full Name -->
                                    <div>
                                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-user mr-1 text-gray-400"></i>
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                               id="full_name" 
                                               name="full_name" 
                                               placeholder="Enter full name"
                                               required>
                                    </div>
                                    
                                    <!-- ID Number -->
                                    <div>
                                        <label for="id_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-id-card mr-1 text-gray-400"></i>
                                            National ID / Passport <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                               id="id_number" 
                                               name="id_number" 
                                               placeholder="Enter ID/Passport number"
                                               required>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-envelope mr-1 text-gray-400"></i>
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" 
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                               id="email" 
                                               name="email" 
                                               placeholder="owner@example.com"
                                               required>
                                        <p class="mt-1.5 text-xs text-gray-500">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            This email will be used for login credentials
                                        </p>
                                    </div>
                                    
                                    <!-- Phone -->
                                    <div>
                                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-phone mr-1 text-gray-400"></i>
                                            Phone Number <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" 
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                               id="phone" 
                                               name="phone" 
                                               placeholder="+256 700 000 000"
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div class="mt-6">
                                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                        Physical Address <span class="text-red-500">*</span>
                                    </label>
                                    <textarea class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter physical address"
                                              required></textarea>
                                </div>
                                
                                <!-- Business and Role -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                    <!-- Business -->
                                    <div>
                                        <label for="business_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-building mr-1 text-gray-400"></i>
                                            Assign to Business <span class="text-red-500">*</span>
                                        </label>
                                        <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                                id="business_id" 
                                                name="business_id" 
                                                required>
                                            <option value="">Select Business</option>
                                            <?php if ($businesses_result && $businesses_result->num_rows > 0): ?>
                                                <?php while ($business = $businesses_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $business['id']; ?>">
                                                        <?php echo htmlspecialchars($business['business_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Business Role -->
                                    <div>
                                        <label for="business_role" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-briefcase mr-1 text-gray-400"></i>
                                            Business Role <span class="text-red-500">*</span>
                                        </label>
                                        <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                                id="business_role" 
                                                name="business_role" 
                                                required>
                                            <option value="">Select Role</option>
                                            <option value="Primary Owner">Primary Owner</option>
                                            <option value="Co-Owner">Co-Owner</option>
                                            <option value="Managing Director">Managing Director</option>
                                            <option value="Director">Director</option>
                                            <option value="Partner">Partner</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Form Actions -->
                                <div class="flex items-center gap-3 mt-8 pt-6 border-t border-gray-200">
                                    <button type="submit" 
                                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        <span class="font-medium">Register Owner</span>
                                    </button>
                                    <a href="owners.php" 
                                       class="inline-flex items-center px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">
                                        <i class="fas fa-times mr-2"></i>
                                        <span class="font-medium">Cancel</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 8 seconds (longer for success messages with credentials)
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                const isSuccess = alert.classList.contains('bg-green-50');
                const timeout = isSuccess ? 15000 : 5000; // 15s for success, 5s for errors
                
                setTimeout(() => {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => alert.remove(), 500);
                }, timeout);
            });
            
            // Form animation
            const form = document.querySelector('form');
            if (form) {
                form.style.opacity = '0';
                form.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    form.style.transition = 'all 0.5s ease';
                    form.style.opacity = '1';
                    form.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>