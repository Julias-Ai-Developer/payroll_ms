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

// Fetch owner details
$sql = "SELECT id, full_name, username, email FROM business_owners WHERE id = ?";
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_sql = "UPDATE business_owners SET password = ?, updated_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $owner_id);
        
        if ($update_stmt->execute()) {
            $update_stmt->close();
            header('Location: owners.php?success=' . urlencode('Password reset successfully for ' . $owner['full_name']));
            exit();
        } else {
            $errors[] = "Failed to reset password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo htmlspecialchars($owner['full_name']); ?></title>
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
        .bg-yellow-gradient {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
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
                        <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
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
                                <i class="fas fa-key text-yellow-600 mr-3"></i>
                                Reset Password
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
                                    <li class="text-gray-600 font-medium">Reset Password</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="owners.php" class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-200 shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                    </div>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r-lg shadow-sm">
                            <div class="flex">
                                <i class="fas fa-exclamation-circle mr-3 text-red-500 text-xl"></i>
                                <div>
                                    <p class="font-medium">Please fix the following errors:</p>
                                    <ul class="list-disc list-inside mt-2">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Reset Password Form -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-yellow-gradient px-6 py-5">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user-shield text-white text-xl"></i>
                                </div>
                                <div class="text-white">
                                    <h2 class="text-xl font-bold">Reset Password</h2>
                                    <p class="text-yellow-100 text-sm">Set a new password for this owner account</p>
                                </div>
                            </div>
                        </div>

                        <!-- Owner Information -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-14 h-14 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-user-tie text-yellow-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($owner['full_name']); ?></h3>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                                        <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($owner['username']); ?></span>
                                        <span><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($owner['email']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form method="POST" class="p-6" id="resetPasswordForm">
                            <!-- Security Notice -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <i class="fas fa-shield-alt text-blue-600 text-xl mr-3 mt-0.5"></i>
                                    <div>
                                        <h4 class="font-medium text-blue-900 mb-1">Security Notice</h4>
                                        <p class="text-sm text-blue-800">
                                            You are about to reset the password for this business owner account. 
                                            The new password will be hashed and securely stored. Make sure to communicate 
                                            the new password to the owner through a secure channel.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Fields -->
                            <div class="space-y-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        New Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               name="new_password" 
                                               id="new_password"
                                               required 
                                               minlength="6"
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                               placeholder="Enter new password">
                                        <button type="button" 
                                                onclick="togglePassword('new_password', 'toggleIcon1')"
                                                class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-eye" id="toggleIcon1"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Minimum 6 characters required
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm New Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               name="confirm_password" 
                                               id="confirm_password"
                                               required 
                                               minlength="6"
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                               placeholder="Confirm new password">
                                        <button type="button" 
                                                onclick="togglePassword('confirm_password', 'toggleIcon2')"
                                                class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-eye" id="toggleIcon2"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="mt-1 text-xs"></div>
                                </div>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Password Strength:</span>
                                    <span id="strengthText" class="text-sm font-medium"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="strengthBar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-lg hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 shadow-lg font-medium">
                                    <i class="fas fa-key mr-2"></i>
                                    Reset Password
                                </button>
                                <a href="owners.php" 
                                   class="inline-flex items-center px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                                <a href="owner_view.php?id=<?php echo $owner_id; ?>" 
                                   class="inline-flex items-center px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-all duration-200">
                                    <i class="fas fa-eye mr-2"></i>
                                    View Owner
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let color = '';
            let text = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    color = 'bg-red-500';
                    text = 'Weak';
                    strengthBar.style.width = '20%';
                    break;
                case 2:
                    color = 'bg-orange-500';
                    text = 'Fair';
                    strengthBar.style.width = '40%';
                    break;
                case 3:
                    color = 'bg-yellow-500';
                    text = 'Good';
                    strengthBar.style.width = '60%';
                    break;
                case 4:
                    color = 'bg-blue-500';
                    text = 'Strong';
                    strengthBar.style.width = '80%';
                    break;
                case 5:
                    color = 'bg-green-500';
                    text = 'Very Strong';
                    strengthBar.style.width = '100%';
                    break;
            }
            
            strengthBar.className = 'h-2 rounded-full transition-all duration-300 ' + color;
            strengthText.textContent = text;
            strengthText.className = 'text-sm font-medium ' + color.replace('bg-', 'text-');
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchDiv.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i><span class="text-green-600">Passwords match</span>';
                } else {
                    matchDiv.innerHTML = '<i class="fas fa-times-circle text-red-500 mr-1"></i><span class="text-red-600">Passwords do not match</span>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        });

        // Form validation
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            return confirm('Are you sure you want to reset the password for this owner?');
        });
    </script>
</body>
</html>