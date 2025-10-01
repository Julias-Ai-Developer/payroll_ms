<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

$success = $error = '';
$business = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: business.php?error=Invalid business ID");
    exit;
}

$business_id = $_GET['id'];

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
    $status = trim($_POST['status']);
    
    // Validate input
    if (empty($business_name) || empty($registration_number) || empty($business_type) || 
        empty($address) || empty($phone) || empty($email) || empty($registration_date)) {
        $error = "Please fill all required fields";
    } else {
        // Check if registration number exists for other businesses
        $check_sql = "SELECT id FROM businesses WHERE registration_number = ? AND id != ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("si", $registration_number, $business_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "A business with this registration number already exists";
            } else {
                // Update business
                $update_sql = "UPDATE businesses SET 
                              business_name = ?, 
                              registration_number = ?, 
                              business_type = ?, 
                              address = ?, 
                              phone = ?, 
                              email = ?, 
                              registration_date = ?,
                              status = ?
                              WHERE id = ?";
                
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("ssssssssi", $business_name, $registration_number, $business_type, 
                                          $address, $phone, $email, $registration_date, $status, $business_id);
                    
                    if ($update_stmt->execute()) {
                        // Log the action
                        logAdminAction($conn, "Business Update", "Updated business: $business_name");
                        
                        // Redirect to business list with success message
                        header("Location: business.php?success=Business updated successfully");
                        exit;
                    } else {
                        $error = "Error: " . $update_stmt->error;
                    }
                    
                    $update_stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
            
            $check_stmt->close();
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
    <title>Edit Business - Payroll Management System</title>
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
                        <a class="nav-link active" href="business.php">
                            <i class="fas fa-building mr-2"></i> Businesses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="owners.php">
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
                        <h2>Edit Business</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="business.php">Businesses</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit Business</li>
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
                        <h5>Business Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $business_id); ?>" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="business_name">Business Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo htmlspecialchars($business['business_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="registration_number">Registration Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($business['registration_number']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="business_type">Business Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="business_type" name="business_type" required>
                                            <option value="">Select Type</option>
                                            <option value="Company" <?php echo ($business['business_type'] == 'Company') ? 'selected' : ''; ?>>Company</option>
                                            <option value="Sole Proprietorship" <?php echo ($business['business_type'] == 'Sole Proprietorship') ? 'selected' : ''; ?>>Sole Proprietorship</option>
                                            <option value="Partnership" <?php echo ($business['business_type'] == 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                            <option value="NGO" <?php echo ($business['business_type'] == 'NGO') ? 'selected' : ''; ?>>NGO</option>
                                            <option value="Other" <?php echo ($business['business_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="registration_date">Date of Registration <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="registration_date" name="registration_date" value="<?php echo htmlspecialchars($business['registration_date']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($business['address']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($business['phone']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($business['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active" <?php echo ($business['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($business['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Update Business</button>
                                <a href="business.php" class="btn btn-secondary ml-2">Cancel</a>
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