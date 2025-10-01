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
                        $success = "Owner registered successfully! <br><br>
                                   <div class='alert alert-info'>
                                   <strong>Login Credentials:</strong><br>
                                   Username: $username<br>
                                   Temporary Password: $temp_password<br><br>
                                   <strong>Important:</strong> Please save or share these credentials securely with the business owner.
                                   </div>";
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h4>Admin Panel</h4>
                    <p>Payroll Management</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="business.php">
                            <i class="fas fa-building mr-2"></i> Businesses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="owners.php">
                            <i class="fas fa-user-tie mr-2"></i> Business Owners
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="audit_logs.php">
                            <i class="fas fa-history mr-2"></i> Audit Logs
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h2>Register New Business Owner</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="owners.php">Business Owners</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Register Owner</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Owner Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_number">National ID / Passport Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="id_number" name="id_number" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <small class="form-text text-muted">This email will be used for login credentials.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="business_id">Business <span class="text-danger">*</span></label>
                                        <select class="form-control" id="business_id" name="business_id" required>
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
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="business_role">Business Role <span class="text-danger">*</span></label>
                                        <select class="form-control" id="business_role" name="business_role" required>
                                            <option value="">Select Role</option>
                                            <option value="Primary Owner">Primary Owner</option>
                                            <option value="Co-Owner">Co-Owner</option>
                                            <option value="Managing Director">Managing Director</option>
                                            <option value="Director">Director</option>
                                            <option value="Partner">Partner</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Register Owner</button>
                                <a href="owners.php" class="btn btn-secondary ml-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>