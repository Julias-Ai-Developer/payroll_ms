<?php
session_start();
require_once '../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
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
                        if (password_verify($password, $hashed_password)) {
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

                            header("location: ../index.php");
                            exit;
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
    <title>Login - Payroll Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Toast notification library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
    </style>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-primary-700">Payroll Management System</h1>
            <p class="text-gray-600">Login to access your dashboard</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">&copy;Payroll Management System. All Rights Reserved || Group E & Kamatrust Ai</p>
        </div>
    </div>
</body>
</html>