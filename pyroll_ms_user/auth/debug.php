<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$success = '';
$error = '';
$token_valid = false;
$token = '';
$user_id = '';
$email = '';
$full_name = '';
$debug_info = []; // Debug information

// Check if token is provided in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    $debug_info[] = "Token received: " . substr($token, 0, 20) . "...";
    $debug_info[] = "Token length: " . strlen($token);
    
    $conn = getConnection();
    
    // First, let's check if the token exists at all
    $check_sql = "SELECT token, expiry, email FROM password_resets WHERE token = ?";
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("s", $token);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $check_stmt->bind_result($db_token, $db_expiry, $db_email);
            $check_stmt->fetch();
            $debug_info[] = "✓ Token found in database";
            $debug_info[] = "Email: " . $db_email;
            $debug_info[] = "Expiry: " . $db_expiry;
            $debug_info[] = "Current time: " . date('Y-m-d H:i:s');
            
            // Check if expired
            if (strtotime($db_expiry) < time()) {
                $debug_info[] = "✗ Token has EXPIRED";
                $time_diff = time() - strtotime($db_expiry);
                $debug_info[] = "Expired " . floor($time_diff / 60) . " minutes ago";
            } else {
                $debug_info[] = "✓ Token is still valid (not expired)";
            }
        } else {
            $debug_info[] = "✗ Token NOT found in database";
        }
        $check_stmt->close();
    }
    
    // Now try the full query with JOIN
    $sql = "SELECT pr.user_id, pr.email, pr.expiry, bo.full_name 
            FROM password_resets pr 
            JOIN business_owners bo ON pr.user_id = bo.id 
            WHERE pr.token = ? AND pr.expiry > NOW()";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            $debug_info[] = "Query executed successfully";
            $debug_info[] = "Rows returned: " . $stmt->num_rows;
            
            if ($stmt->num_rows == 1) {
                $token_valid = true;
                $stmt->bind_result($user_id, $email, $expiry, $full_name);
                $stmt->fetch();
                $debug_info[] = "✓ TOKEN VALID - Form will be shown";
            } else {
                $error = "Invalid or expired reset link. Please request a new password reset.";
                $debug_info[] = "✗ Token validation failed (JOIN query returned 0 rows)";
            }
        } else {
            $debug_info[] = "✗ Query execution failed: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $error = "No reset token provided. Please use the link from your email.";
    $debug_info[] = "✗ No token in URL";
}

// Process password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = trim($_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!preg_match("/[A-Z]/", $new_password)) {
        $error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match("/[a-z]/", $new_password)) {
        $error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match("/[0-9]/", $new_password)) {
        $error = "Password must contain at least one number";
    } else {
        $conn = getConnection();
        
        // Verify token again before updating
        $verify_sql = "SELECT pr.user_id, pr.email, bo.full_name 
                       FROM password_resets pr 
                       JOIN business_owners bo ON pr.user_id = bo.id 
                       WHERE pr.token = ? AND pr.expiry > NOW()";
        
        if ($verify_stmt = $conn->prepare($verify_sql)) {
            $verify_stmt->bind_param("s", $token);
            
            if ($verify_stmt->execute()) {
                $verify_stmt->store_result();
                
                if ($verify_stmt->num_rows == 1) {
                    $verify_stmt->bind_result($user_id, $email, $full_name);
                    $verify_stmt->fetch();
                    $verify_stmt->close();
                    
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $update_sql = "UPDATE business_owners SET password = ? WHERE id = ?";
                    
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param("si", $hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            // Delete used token
                            $delete_sql = "DELETE FROM password_resets WHERE token = ?";
                            if ($delete_stmt = $conn->prepare($delete_sql)) {
                                $delete_stmt->bind_param("s", $token);
                                $delete_stmt->execute();
                                $delete_stmt->close();
                            }
                            
                            $success = "Password reset successful! You can now login with your new password.";
                            $token_valid = false;
                            
                        } else {
                            $error = "Failed to update password. Please try again.";
                        }
                        $update_stmt->close();
                    }
                } else {
                    $error = "Invalid or expired reset link. Please request a new one.";
                }
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PayrollPro (Debug)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .password-strength {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-10 text-center">
            <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Reset Password (Debug Mode)</h1>
            <p class="text-primary-100 text-sm">Troubleshooting token validation</p>
        </div>

        <!-- Form Section -->
        <div class="px-8 py-8 bg-gray-50">
            
            <!-- Debug Information -->
            <?php if (!empty($debug_info)): ?>
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-yellow-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Debug Information
                    </h3>
                    <ul class="text-xs text-yellow-900 space-y-1 font-mono">
                        <?php foreach ($debug_info as $info): ?>
                            <li><?php echo htmlspecialchars($info); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="login.php" class="inline-flex items-center justify-center w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150">
                        Go to Login Page
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
                <?php if (strpos($error, 'expired') !== false || strpos($error, 'Invalid') !== false): ?>
                    <div class="text-center mb-6">
                        <a href="forgot_password.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-150">
                            Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($token_valid && empty($success)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <strong>Hi <?php echo htmlspecialchars($full_name); ?>!</strong><br>
                        Please create a strong password for your account.
                    </p>
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" 
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150" 
                               placeholder="Enter new password" required>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strength-bar"></div>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150" 
                           placeholder="Confirm new password" required>
                    <p class="text-xs mt-2" id="match-text"></p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm font-semibold mb-2">Password must contain:</p>
                    <ul class="text-xs space-y-1" id="requirements">
                        <li id="req-length">○ At least 8 characters</li>
                        <li id="req-upper">○ One uppercase letter</li>
                        <li id="req-lower">○ One lowercase letter</li>
                        <li id="req-number">○ One number</li>
                    </ul>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150">
                    Reset Password
                </button>
            </form>
            <?php endif; ?>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <a href="login.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm">
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Password strength and match checking
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function(e) {
                const password = e.target.value;
                const strengthBar = document.getElementById('strength-bar');
                let strength = 0;
                let color = '#ef4444';
                
                if (password.length >= 8) {
                    strength += 25;
                    document.getElementById('req-length').innerHTML = '<span style="color: green">✓</span> At least 8 characters';
                } else {
                    document.getElementById('req-length').innerHTML = '○ At least 8 characters';
                }
                
                if (/[A-Z]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-upper').innerHTML = '<span style="color: green">✓</span> One uppercase letter';
                } else {
                    document.getElementById('req-upper').innerHTML = '○ One uppercase letter';
                }
                
                if (/[a-z]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-lower').innerHTML = '<span style="color: green">✓</span> One lowercase letter';
                } else {
                    document.getElementById('req-lower').innerHTML = '○ One lowercase letter';
                }
                
                if (/[0-9]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-number').innerHTML = '<span style="color: green">✓</span> One number';
                } else {
                    document.getElementById('req-number').innerHTML = '○ One number';
                }
                
                if (strength >= 100) color = '#10b981';
                else if (strength >= 75) color = '#3b82f6';
                else if (strength >= 50) color = '#f59e0b';
                
                strengthBar.style.width = strength + '%';
                strengthBar.style.backgroundColor = color;
            });
        }
        
        if (confirmInput) {
            confirmInput.addEventListener('input', function(e) {
                const password = passwordInput.value;
                const confirm = e.target.value;
                const matchText = document.getElementById('match-text');
                
                if (confirm === '') {
                    matchText.innerHTML = '';
                } else if (password === confirm) {
                    matchText.innerHTML = '<span style="color: green">✓ Passwords match</span>';
                } else {
                    matchText.innerHTML = '<span style="color: red">✗ Passwords do not match</span>';
                }
            });
        }
    </script>
</body>
</html>