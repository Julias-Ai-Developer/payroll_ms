<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$success = '';
$error = '';
$token_valid = false;
$token = '';
$user_id = '';
$email = '';
$full_name = '';

// Check if token is provided in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    $conn = getConnection();

    // Verify token and check if it's not expired
    $sql = "SELECT pr.user_id, pr.email, pr.expiry, bo.full_name 
            FROM password_resets pr 
            JOIN business_owners bo ON pr.user_id = bo.id 
            WHERE pr.token = ? AND pr.expiry > NOW()";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $token_valid = true;
                $stmt->bind_result($user_id, $email, $expiry, $full_name);
                $stmt->fetch();
            } else {
                $error = "Invalid or expired reset link. Please request a new password reset.";
            }
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $error = "No reset token provided. Please use the link from your email.";
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
               WHERE pr.token = ? 
               AND pr.expiry > NOW()";

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
    <title>Reset Password - PayrollPro</title>
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
            transition: all 0.3s;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .requirement-item {
            transition: all 0.2s ease;
        }

        .requirement-met {
            color: #059669;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-10 text-center">
            <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Reset Password</h1>
            <p class="text-primary-100 text-sm">Create a new secure password for your account</p>
        </div>

        <!-- Form Section -->
        <div class="px-8 py-8 bg-gray-50">
            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 animate-slide-in">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($success); ?></p>
                            <p class="text-sm mt-2">You can now login to your account with your new password.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="login.php" class="inline-flex items-center justify-center w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Go to Login Page
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 animate-slide-in">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
                <?php if (strpos($error, 'expired') !== false || strpos($error, 'Invalid') !== false): ?>
                    <div class="text-center mb-6">
                        <a href="forgot_password.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($token_valid && empty($success)): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <!-- Welcome Message -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            <strong>Hi <?php echo htmlspecialchars($full_name); ?>!</strong><br>
                            Please create a strong password for your account.
                        </p>
                    </div>

                    <!-- New Password Field -->
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="new_password" name="new_password"
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150 bg-white text-gray-900"
                                placeholder="Enter new password"
                                required
                                autocomplete="new-password">
                            <button type="button" onclick="togglePassword('new_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eye-new_password">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strength-bar"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2" id="strength-text">Password strength: <span class="font-medium">Not set</span></p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150 bg-white text-gray-900"
                                placeholder="Confirm new password"
                                required
                                autocomplete="new-password">
                            <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eye-confirm_password">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs mt-2" id="match-text"></p>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700 font-semibold mb-3">Password Requirements:</p>
                        <ul class="text-xs text-gray-600 space-y-2">
                            <li class="flex items-center requirement-item" id="req-length">
                                <span class="mr-2 text-gray-400">○</span> At least 8 characters long
                            </li>
                            <li class="flex items-center requirement-item" id="req-upper">
                                <span class="mr-2 text-gray-400">○</span> One uppercase letter (A-Z)
                            </li>
                            <li class="flex items-center requirement-item" id="req-lower">
                                <span class="mr-2 text-gray-400">○</span> One lowercase letter (a-z)
                            </li>
                            <li class="flex items-center requirement-item" id="req-number">
                                <span class="mr-2 text-gray-400">○</span> One number (0-9)
                            </li>
                        </ul>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition duration-150 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Reset Password
                        </span>
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <a href="login.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-150 group">
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById('eye-' + fieldId);

            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('new_password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function(e) {
                const password = e.target.value;
                const strengthBar = document.getElementById('strength-bar');
                const strengthText = document.getElementById('strength-text');

                let strength = 0;
                let strengthLabel = 'Weak';
                let color = '#ef4444';

                // Check length
                if (password.length >= 8) {
                    strength += 25;
                    document.getElementById('req-length').innerHTML = '<span class="mr-2 text-green-600">✓</span> At least 8 characters long';
                    document.getElementById('req-length').classList.add('requirement-met');
                } else {
                    document.getElementById('req-length').innerHTML = '<span class="mr-2 text-gray-400">○</span> At least 8 characters long';
                    document.getElementById('req-length').classList.remove('requirement-met');
                }

                // Check uppercase
                if (/[A-Z]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-upper').innerHTML = '<span class="mr-2 text-green-600">✓</span> One uppercase letter (A-Z)';
                    document.getElementById('req-upper').classList.add('requirement-met');
                } else {
                    document.getElementById('req-upper').innerHTML = '<span class="mr-2 text-gray-400">○</span> One uppercase letter (A-Z)';
                    document.getElementById('req-upper').classList.remove('requirement-met');
                }

                // Check lowercase
                if (/[a-z]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-lower').innerHTML = '<span class="mr-2 text-green-600">✓</span> One lowercase letter (a-z)';
                    document.getElementById('req-lower').classList.add('requirement-met');
                } else {
                    document.getElementById('req-lower').innerHTML = '<span class="mr-2 text-gray-400">○</span> One lowercase letter (a-z)';
                    document.getElementById('req-lower').classList.remove('requirement-met');
                }

                // Check number
                if (/[0-9]/.test(password)) {
                    strength += 25;
                    document.getElementById('req-number').innerHTML = '<span class="mr-2 text-green-600">✓</span> One number (0-9)';
                    document.getElementById('req-number').classList.add('requirement-met');
                } else {
                    document.getElementById('req-number').innerHTML = '<span class="mr-2 text-gray-400">○</span> One number (0-9)';
                    document.getElementById('req-number').classList.remove('requirement-met');
                }

                // Determine strength label and color
                if (strength >= 100) {
                    strengthLabel = 'Strong';
                    color = '#10b981';
                } else if (strength >= 75) {
                    strengthLabel = 'Good';
                    color = '#3b82f6';
                } else if (strength >= 50) {
                    strengthLabel = 'Medium';
                    color = '#f59e0b';
                }

                strengthBar.style.width = strength + '%';
                strengthBar.style.backgroundColor = color;
                strengthText.innerHTML = 'Password strength: <span class="font-medium" style="color: ' + color + '">' + strengthLabel + '</span>';
            });
        }

        // Password match checker
        const confirmInput = document.getElementById('confirm_password');
        if (confirmInput) {
            confirmInput.addEventListener('input', function(e) {
                const password = document.getElementById('new_password').value;
                const confirmPassword = e.target.value;
                const matchText = document.getElementById('match-text');

                if (confirmPassword === '') {
                    matchText.innerHTML = '';
                } else if (password === confirmPassword) {
                    matchText.innerHTML = '<span class="text-green-600 font-medium">✓ Passwords match</span>';
                } else {
                    matchText.innerHTML = '<span class="text-red-600 font-medium">✗ Passwords do not match</span>';
                }
            });
        }

        // Also check on password field change
        if (passwordInput && confirmInput) {
            passwordInput.addEventListener('input', function() {
                const confirmPassword = document.getElementById('confirm_password').value;
                if (confirmPassword) {
                    confirmInput.dispatchEvent(new Event('input'));
                }
            });
        }
    </script>
</body>

</html>