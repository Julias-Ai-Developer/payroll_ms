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

// Get recent activities (you can customize this query based on your audit_logs table)
$recent_activities = [];
$sql = "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Payroll Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'roboto': ['"Roboto Condensed"', 'sans-serif']
                    },
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
        
        /* Custom scrollbar */
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
        
        /* Custom gradient background for sidebar */
        .bg-blue-gradient {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        }
        
        /* Animated gradient cards */
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
        }
        
        /* Card hover effects */
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        /* Pulse animation for stats */
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-roboto antialiased">
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
                        <a href="dashboard.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg shadow-lg backdrop-blur-sm transform transition-all duration-200 hover:scale-105">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
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
                        <a href="owners.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
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
                        <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
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
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Businesses Card -->
                        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden relative">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="p-6 relative">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Total Businesses</p>
                                        <h3 class="text-4xl font-bold text-white mt-2"><?php echo $stats['businesses']; ?></h3>
                                    </div>
                                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-building text-2xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex items-center text-blue-100 text-sm">
                                    <i class="fas fa-arrow-up mr-2"></i>
                                    <span>Active businesses registered</span>
                                </div>
                            </div>
                            <div class="h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                        </div>
                        
                        <!-- Business Owners Card -->
                        <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg overflow-hidden relative">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="p-6 relative">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Business Owners</p>
                                        <h3 class="text-4xl font-bold text-white mt-2"><?php echo $stats['owners']; ?></h3>
                                    </div>
                                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user-tie text-2xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex items-center text-green-100 text-sm">
                                    <i class="fas fa-users mr-2"></i>
                                    <span>Registered business owners</span>
                                </div>
                            </div>
                            <div class="h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
                        </div>
                        
                        <!-- Employees Card -->
                        <div class="stat-card bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl shadow-lg overflow-hidden relative">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="p-6 relative">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-yellow-100 text-sm font-medium uppercase tracking-wide">Total Employees</p>
                                        <h3 class="text-4xl font-bold text-white mt-2"><?php echo $stats['employees']; ?></h3>
                                    </div>
                                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-users text-2xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex items-center text-yellow-100 text-sm">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    <span>Employees across all businesses</span>
                                </div>
                            </div>
                            <div class="h-1 bg-gradient-to-r from-yellow-400 to-orange-500"></div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Section -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h5 class="text-lg font-bold text-gray-800 flex items-center">
                                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <a href="business_register.php" class="group relative bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg p-6 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h6 class="font-bold text-lg mb-1">Register Business</h6>
                                            <p class="text-blue-100 text-sm">Add a new business</p>
                                        </div>
                                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-plus-circle text-2xl"></i>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="owner_register.php" class="group relative bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg p-6 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h6 class="font-bold text-lg mb-1">Add Owner</h6>
                                            <p class="text-green-100 text-sm">Register business owner</p>
                                        </div>
                                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-user-plus text-2xl"></i>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="../../index.php" class="group relative bg-gradient-to-br from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg p-6 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h6 class="font-bold text-lg mb-1">Main Site</h6>
                                            <p class="text-gray-300 text-sm">View public website</p>
                                        </div>
                                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-external-link-alt text-2xl"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity Section -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h5 class="text-lg font-bold text-gray-800 flex items-center">
                                <i class="fas fa-clock mr-2 text-blue-500"></i>
                                Recent Activity
                            </h5>
                        </div>
                        <div class="p-6">
                            <?php if (!empty($recent_activities)): ?>
                                <div class="space-y-4">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-circle text-blue-600 text-xs"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-800 font-medium"><?php echo htmlspecialchars($activity['action'] ?? 'Activity'); ?></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?php echo isset($activity['created_at']) ? date('M d, Y H:i', strtotime($activity['created_at'])) : 'N/A'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-inbox text-3xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No recent activity</p>
                                    <p class="text-sm text-gray-400 mt-1">Activity will appear here once actions are performed</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
            
            // Add animation to stat cards on page load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
</body>
</html>