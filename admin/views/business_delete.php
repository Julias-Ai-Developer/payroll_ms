<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: business.php?error=Invalid business ID");
    exit;
}

$business_id = $_GET['id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'deactivate';

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

// Process deletion or deactivation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        if ($action == 'delete') {
            // Check if there are owners linked to this business
            $check_owners = "SELECT COUNT(*) as count FROM business_owners WHERE business_id = ?";
            if ($check_stmt = $conn->prepare($check_owners)) {
                $check_stmt->bind_param("i", $business_id);
                $check_stmt->execute();
                $owner_result = $check_stmt->get_result();
                $owner_count = $owner_result->fetch_assoc()['count'];
                $check_stmt->close();
                
                if ($owner_count > 0) {
                    header("Location: business.php?error=Cannot delete business with linked owners. Remove owners first or deactivate instead.");
                    exit;
                }
                
                // Check if there are employees linked to this business
                $check_employees = "SELECT COUNT(*) as count FROM employees WHERE business_id = ?";
                if ($check_emp_stmt = $conn->prepare($check_employees)) {
                    $check_emp_stmt->bind_param("i", $business_id);
                    $check_emp_stmt->execute();
                    $emp_result = $check_emp_stmt->get_result();
                    $emp_count = $emp_result->fetch_assoc()['count'];
                    $check_emp_stmt->close();
                    
                    if ($emp_count > 0) {
                        header("Location: business.php?error=Cannot delete business with linked employees. Remove employees first or deactivate instead.");
                        exit;
                    }
                    
                    // Delete the business
                    $delete_sql = "DELETE FROM businesses WHERE id = ?";
                    if ($delete_stmt = $conn->prepare($delete_sql)) {
                        $delete_stmt->bind_param("i", $business_id);
                        
                        if ($delete_stmt->execute()) {
                            // Log the action
                            logAdminAction($conn, "Business Deletion", "Deleted business: {$business['business_name']}");
                            
                            // Redirect to business list with success message
                            header("Location: business.php?success=Business deleted successfully");
                            exit;
                        } else {
                            header("Location: business.php?error=Error deleting business: " . $delete_stmt->error);
                            exit;
                        }
                        
                        $delete_stmt->close();
                    } else {
                        header("Location: business.php?error=Error preparing delete statement: " . $conn->error);
                        exit;
                    }
                } else {
                    header("Location: business.php?error=Error checking employees: " . $conn->error);
                    exit;
                }
            } else {
                header("Location: business.php?error=Error checking owners: " . $conn->error);
                exit;
            }
        } else {
            // Deactivate the business
            $status = 'inactive';
            $update_sql = "UPDATE businesses SET status = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("si", $status, $business_id);
                
                if ($update_stmt->execute()) {
                    // Log the action
                    logAdminAction($conn, "Business Deactivation", "Deactivated business: {$business['business_name']}");
                    
                    // Redirect to business list with success message
                    header("Location: business.php?success=Business deactivated successfully");
                    exit;
                } else {
                    header("Location: business.php?error=Error deactivating business: " . $update_stmt->error);
                    exit;
                }
                
                $update_stmt->close();
            } else {
                header("Location: business.php?error=Error preparing update statement: " . $conn->error);
                exit;
            }
        }
    } else {
        // User canceled the action
        header("Location: business.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($action); ?> Business - Payroll Management System</title>
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
                        <h2><?php echo ucfirst($action); ?> Business</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="business.php">Businesses</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst($action); ?> Business</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Confirm <?php echo ucfirst($action); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i> Warning!</h5>
                            <?php if ($action == 'delete'): ?>
                                <p>You are about to permanently delete the business "<strong><?php echo htmlspecialchars($business['business_name']); ?></strong>".</p>
                                <p>This action cannot be undone. All data related to this business will be permanently removed.</p>
                            <?php else: ?>
                                <p>You are about to deactivate the business "<strong><?php echo htmlspecialchars($business['business_name']); ?></strong>".</p>
                                <p>The business will be marked as inactive but all data will be preserved. You can reactivate it later.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6>Business Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Business Name</th>
                                        <td><?php echo htmlspecialchars($business['business_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Registration Number</th>
                                        <td><?php echo htmlspecialchars($business['registration_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Business Type</th>
                                        <td><?php echo htmlspecialchars($business['business_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Registration Date</th>
                                        <td><?php echo htmlspecialchars($business['registration_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge badge-<?php echo ($business['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($business['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $business_id . "&action=" . $action); ?>" method="post">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm" name="confirm" value="yes" required>
                                    <label class="custom-control-label" for="confirm">
                                        I confirm that I want to <?php echo $action; ?> this business
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-<?php echo ($action == 'delete') ? 'trash-alt' : 'ban'; ?> mr-2"></i>
                                    <?php echo ucfirst($action); ?> Business
                                </button>
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