<?php
session_start();
require_once '../config/database.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../../vendor/autoload.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$success = '';
$error = '';

// Process forgot password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Please enter your email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        $conn = getConnection();

        // Check if email exists
        $sql = "SELECT id, email, full_name FROM business_owners WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id, $user_email, $full_name);
                    $stmt->fetch();
                    $stmt->close();

                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Store token in database
                    $insert_sql = "INSERT INTO password_resets (user_id, email, token, expiry) 
                                   VALUES (?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE token = VALUES(token), expiry = VALUES(expiry)";

                    if ($insert_stmt = $conn->prepare($insert_sql)) {
                        $insert_stmt->bind_param("isss", $user_id, $user_email, $token, $expiry);

                        if ($insert_stmt->execute()) {
                            // Generate reset link
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                            $reset_link = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

                            // Create PHPMailer instance
                            $mail = new PHPMailer(true);

                            try {
                                // Server settings
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';  // CHANGE THIS to your SMTP host
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'muyambijulias@gmail.com';  // CHANGE THIS to your email
                                $mail->Password   = 'mrdelhmwvvlkxthl';     // CHANGE THIS to your app password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                // Disable SSL verification (only for testing - remove in production)
                                $mail->SMTPOptions = array(
                                    'ssl' => array(
                                        'verify_peer' => false,
                                        'verify_peer_name' => false,
                                        'allow_self_signed' => true
                                    )
                                );

                                // Recipients
                                $mail->setFrom('noreply@payrollpro.com', 'PayrollPro');
                                $mail->addAddress($user_email, $full_name);
                                $mail->addReplyTo('support@payrollpro.com', 'PayrollPro Support');

                                // Content
                                $mail->isHTML(true);
                                $mail->CharSet = 'UTF-8';
                                $mail->Subject = 'Password Reset Request - PayrollPro';

                                // HTML email body
                                $mail->Body = "
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <meta charset='UTF-8'>
                                    <style>
                                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                        .header { background: linear-gradient(to right, #0284c7, #0369a1); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                                        .header h1 { color: white; margin: 0; font-size: 28px; }
                                        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                                        .button { display: inline-block; background: #0284c7; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                                        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; }
                                        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 5px; }
                                        .link-box { background: white; padding: 15px; border-radius: 5px; word-break: break-all; border: 1px solid #ddd; margin: 10px 0; }
                                    </style>
                                </head>
                                <body>
                                    <div class='container'>
                                        <div class='header'>
                                            <h1>🔐 Password Reset Request</h1>
                                        </div>
                                        <div class='content'>
                                            <p>Hello <strong>" . htmlspecialchars($full_name) . "</strong>,</p>
                                            
                                            <p>We received a request to reset your password for your PayrollPro account.</p>
                                            
                                            <p>Click the button below to create a new password:</p>
                                            
                                            <p style='text-align: center;'>
                                                <a href='" . htmlspecialchars($reset_link) . "' class='button'>Reset My Password</a>
                                            </p>
                                            
                                            <p>Or copy and paste this link into your browser:</p>
                                            <div class='link-box'>" . htmlspecialchars($reset_link) . "</div>
                                            
                                            <div class='warning'>
                                                <strong>⚠️ Important Security Information:</strong>
                                                <ul style='margin: 10px 0; padding-left: 20px;'>
                                                    <li>This link will expire in <strong>1 hour</strong></li>
                                                    <li>If you didn't request this reset, please ignore this email</li>
                                                    <li>Your password won't change until you create a new one</li>
                                                    <li>Never share this link with anyone</li>
                                                </ul>
                                            </div>
                                            
                                            <p style='margin-top: 30px;'>If you're having trouble, please contact our support team.</p>
                                            
                                            <p style='margin-top: 20px;'>Best regards,<br><strong>The PayrollPro Team</strong></p>
                                        </div>
                                        <div class='footer'>
                                            <p><strong>PayrollPro - Payroll Management System</strong></p>
                                            <p>&copy; " . date('Y') . " All Rights Reserved || Group E</p>
                                        </div>
                                    </div>
                                </body>
                                </html>
                                ";

                                // Plain text alternative
                                $mail->AltBody = "Password Reset Request\n\n"
                                    . "Hello " . $full_name . ",\n\n"
                                    . "We received a request to reset your password.\n\n"
                                    . "Please visit this link to reset your password:\n"
                                    . $reset_link . "\n\n"
                                    . "This link will expire in 1 hour.\n\n"
                                    . "Best regards,\nPayrollPro Team";

                                // Send email
                                $mail->send();
                                $success = "Password reset link has been sent to your email address!";
                            } catch (Exception $e) {
                                // Log error but show generic message for security
                                error_log("Email sending failed: {$mail->ErrorInfo}");
                                $error = "Failed to send reset email. Please contact support. Error: " . $mail->ErrorInfo;
                            }
                        } else {
                            $error = "Something went wrong. Please try again later.";
                        }
                        $insert_stmt->close();
                    }
                } else {
                    // Don't reveal if email doesn't exist for security
                    $success = "If that email is registered, a reset link has been sent.";
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
    <title>Forgot Password - PayrollPro</title>
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
            <h1 class="text-3xl font-bold text-white mb-2">Forgot Password?</h1>
            <p class="text-primary-100 text-sm">No worries, we'll send you reset instructions</p>
        </div>

        <!-- Form Section -->
        <div class="px-8 py-8 bg-gray-50">
            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($success); ?></p>
                            <p class="text-sm mt-1">Please check your inbox (and spam folder). The link will expire in 1 hour.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
                    <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($success)): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150 bg-white text-gray-900 placeholder-gray-400"
                                placeholder="Enter your registered email" required>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition duration-150 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send Reset Link
                        </span>
                    </button>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-green-800">
                                <p class="font-medium">Note:</p>
                                <p class="mt-1">The reset link will expire in 1 hour. If you don't receive an email, check your spam folder.</p>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <a href="login.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>

</html>