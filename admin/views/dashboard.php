<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

// Get statistics
$stats = [
    'businesses' => 0,
    'owners' => 0,
    'employees' => 0
];

// Count businesses
$sql = "SELECT COUNT(*) as count FROM businesses";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['businesses'] = $row['count'];
}

// Count owners
$sql = "SELECT COUNT(*) as count FROM business_owners";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['owners'] = $row['count'];
}

// Count employees
$sql = "SELECT COUNT(*) as count FROM employees";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['employees'] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Payroll Management System</title>
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
        .card-counter {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 5px;
            color: #fff;
        }
        .card-counter i {
            font-size: 4em;
            opacity: 0.4;
        }
        .card-counter .count-numbers {
            position: absolute;
            right: 35px;
            top: 20px;
            font-size: 32px;
            display: block;
        }
        .card-counter .count-name {
            position: absolute;
            right: 35px;
            top: 65px;
            font-style: italic;
            text-transform: capitalize;
            opacity: 0.5;
            display: block;
        }
        .bg-primary {
            background-color: #007bff;
        }
        .bg-success {
            background-color: #28a745;
        }
        .bg-warning {
            background-color: #ffc107;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="business.php">
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
                        <h2>Dashboard</h2>
                        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card-counter bg-primary">
                            <i class="fas fa-building"></i>
                            <span class="count-numbers"><?php echo $stats['businesses']; ?></span>
                            <span class="count-name">Businesses</span>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card-counter bg-success">
                            <i class="fas fa-user-tie"></i>
                            <span class="count-numbers"><?php echo $stats['owners']; ?></span>
                            <span class="count-name">Business Owners</span>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card-counter bg-warning">
                            <i class="fas fa-users"></i>
                            <span class="count-numbers"><?php echo $stats['employees']; ?></span>
                            <span class="count-name">Employees</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <a href="business_register.php" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus-circle mr-2"></i> Register New Business
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="owner_register.php" class="btn btn-success btn-block">
                                            <i class="fas fa-user-plus mr-2"></i> Add Business Owner
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="../../index.php" class="btn btn-secondary btn-block">
                                            <i class="fas fa-external-link-alt mr-2"></i> View Main Site
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
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