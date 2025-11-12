<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

$success = $error = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $business_name = trim($_POST['business_name']);
    $registration_number = trim($_POST['registration_number']);
    $business_type = trim($_POST['business_type']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $registration_date = trim($_POST['registration_date']);
    
    // Validate input
    if (empty($business_name) || empty($registration_number) || empty($business_type) || 
        empty($address) || empty($phone) || empty($email) || empty($registration_date)) {
        $error = "Please fill all required fields";
    } else {
        // Check if business already exists
        $check_sql = "SELECT id FROM businesses WHERE registration_number = ?";
        if ($stmt = $conn->prepare($check_sql)) {
            $stmt->bind_param("s", $registration_number);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "A business with this registration number already exists";
            } else {
                // Insert new business
                $insert_sql = "INSERT INTO businesses (business_name, registration_number, business_type, 
                                address, phone, email, registration_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                if ($insert_stmt = $conn->prepare($insert_sql)) {
                    $insert_stmt->bind_param("sssssss", $business_name, $registration_number, $business_type, 
                                            $address, $phone, $email, $registration_date);
                    
                    if ($insert_stmt->execute()) {
                        $business_id = $insert_stmt->insert_id;
                        
                        // Log the action
                        logAdminAction($conn, "Business Registration", "Registered new business: $business_name");
                        
                        // Redirect with success message
                        header("Location: business.php?success=Business registered successfully!");
                        exit();
                    } else {
                        $error = "Error: " . $insert_stmt->error;
                    }
                    
                    $insert_stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
            
            $stmt->close();
        } else {
            $error = "Error checking business: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Business - Payroll Management System</title>
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
        
        /* Form input focus styles */
        .form-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
        
        /* Smooth transitions */
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
                                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>
                                Register New Business
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
                                    <li class="text-gray-600 font-medium">Register Business</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="business.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                    </div>
                </div>
                
                <!-- Alert Messages -->
                <?php if (!empty($success)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-r-lg shadow-sm animate-fadeIn" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-500 text-xl"></i>
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($success); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r-lg shadow-sm animate-fadeIn" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-red-500 text-xl"></i>
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h5 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-building mr-2"></i>
                            Business Information
                        </h5>
                        <p class="text-blue-100 text-sm mt-1">Enter the details of the new business</p>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="p-6">
                        <!-- Business Name and Registration Number -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="business_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Business Name <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           id="business_name" 
                                           name="business_name" 
                                           class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                           placeholder="Enter business name"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="registration_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Registration Number <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-card text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           id="registration_number" 
                                           name="registration_number" 
                                           class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                           placeholder="Enter registration number"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Type and Registration Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="business_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Business Type <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-briefcase text-gray-400"></i>
                                    </div>
                                    <select id="business_type" 
                                            name="business_type" 
                                            class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all appearance-none bg-white"
                                            required>
                                        <option value="">Select Business Type</option>
                                        <option value="Company">Company</option>
                                        <option value="Sole Proprietorship">Sole Proprietorship</option>
                                        <option value="Partnership">Partnership</option>
                                        <option value="NGO">NGO</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="registration_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Date of Registration <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="far fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input type="date" 
                                           id="registration_date" 
                                           name="registration_date" 
                                           class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-6">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                Business Address <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <textarea id="address" 
                                          name="address" 
                                          rows="3" 
                                          class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all resize-none"
                                          placeholder="Enter complete business address"
                                          required></textarea>
                            </div>
                        </div>
                        
                        <!-- Phone and Email -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                           placeholder="Enter phone number"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-input block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none transition-all"
                                           placeholder="Enter email address"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                All fields marked with <span class="text-red-500">*</span> are required
                            </p>
                            <div class="flex items-center space-x-3">
                                <a href="business.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Register Business
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Help Section -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <h6 class="font-semibold text-blue-900 mb-1">Registration Guidelines</h6>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Ensure the registration number is unique and matches official documents</li>
                                <li>• Provide accurate contact information for business correspondence</li>
                                <li>• The registration date should match the official business registration date</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to form on load
            const formCard = document.querySelector('.bg-white.rounded-xl');
            if (formCard) {
                formCard.style.opacity = '0';
                formCard.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    formCard.style.transition = 'all 0.5s ease';
                    formCard.style.opacity = '1';
                    formCard.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // Form validation and UX improvements
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select, textarea');
            
            // Add focus styling
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
                });
            });
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registering...';
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>