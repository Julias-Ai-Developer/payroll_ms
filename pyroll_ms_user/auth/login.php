<?php
session_start();
require_once '../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $conn = getConnection();

        // Join business_owners with businesses
        $sql = "SELECT 
                    o.id, 
                    o.business_id, 
                    o.id_number, 
                    o.full_name, 
                    o.username, 
                    o.email, 
                    o.phone, 
                    o.address, 
                    o.business_role, 
                    o.password,
                    
                    b.business_name,
                    b.address AS business_address,
                    b.email AS business_email,
                    b.phone AS business_phone,
                    b.registration_number,
                    b.registration_date,
                    b.business_type,
                    b.status
                    
                FROM business_owners o
                INNER JOIN businesses b ON o.business_id = b.id
                WHERE o.username = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {                    
                    $stmt->bind_result(
                        $id, 
                        $business_id, 
                        $id_number, 
                        $full_name, 
                        $db_username, 
                        $email, 
                        $phone, 
                        $address, 
                        $role, 
                        $hashed_password,
                        
                        $business_name,
                        $biz_address,
                        $biz_email,
                        $biz_phone,
                        $registration_number,
                        $registration_date,
                        $business_type,
                        $biz_status
                    );

                    if ($stmt->fetch()) {
                        // Verify password first
                        if (password_verify($password, $hashed_password)) {
                            // Check if business status is active
                            if (strtolower($biz_status) === 'active') {
                                session_regenerate_id(true);
                                
                                // Owner data
                                $_SESSION["loggedin"] = true;
                                $_SESSION["user_id"] = $id;
                                $_SESSION["business_id"] = $business_id;
                                $_SESSION["id_number"] = $id_number;
                                $_SESSION["full_name"] = $full_name;
                                $_SESSION["username"] = $db_username;
                                $_SESSION["email"] = $email;
                                $_SESSION["phone"] = $phone;
                                $_SESSION["address"] = $address;
                                $_SESSION["business_role"] = $role;

                                // Business data
                                $_SESSION["business_name"] = $business_name;
                                $_SESSION["business_address"] = $biz_address;
                                $_SESSION["business_email"] = $biz_email;
                                $_SESSION["business_phone"] = $biz_phone;
                                $_SESSION["registration_number"] = $registration_number;
                                $_SESSION["registration_date"] = $registration_date;
                                $_SESSION["business_type"] = $business_type;
                                $_SESSION["business_status"] = $biz_status;

                                $_SESSION["show_welcome"] = true;

                                header("Location: ../dashboard.php");
                                exit();
                            } else {
                                $error = "Your business account is not active. Please contact support for assistance.";
                            }
                        } else {
                            $error = "Invalid username or password";
                        }
                    }
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }

            $stmt->close();
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
    <title>Login - PayrollPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">PayrollPro</h1>
            <p class="text-primary-100 text-sm">Sign in to manage your payroll system</p>
        </div>

        <!-- Form Section -->
        <div class="px-8 py-8 bg-gray-50">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
                    <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username or Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" id="username" name="username" 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150 bg-white text-gray-900 placeholder-gray-400" 
                               placeholder="Enter your username or email" required>
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition duration-150 bg-white text-gray-900 placeholder-gray-400" 
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <span class="ml-2 text-gray-600">Remember me</span>
                    </label>
                    <a href="../auth/forgot_password.php" class="text-primary-600 hover:text-primary-700 font-medium">Forgot password?</a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition duration-150 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign In
                    </span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-8 py-4 bg-white border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">&copy; Payroll Management System. All Rights Reserved || Group E</p>
        </div>
    </div>
</body>
</html>