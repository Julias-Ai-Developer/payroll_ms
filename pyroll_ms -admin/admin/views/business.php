<?php
require_once '../appLogic/session.php';
require_once '../../config/database.php';

// Require admin login
requireAdminLogin();

// Get all businesses
$sql = "SELECT * FROM businesses ORDER BY created_at DESC";
$result = $conn->query($sql);
$businesses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $businesses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Businesses - Payroll Management System</title>
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
        
        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Table row animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-roboto antialiased">
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
                        <a href="dashboard.php" class="flex items-center px-4 py-3 text-blue-50 rounded-lg transition-all duration-200 hover:bg-white/10 hover:text-white">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-tachometer-alt text-lg"></i>
                            </div>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="business.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg shadow-lg backdrop-blur-sm">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
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
                        <h1 class="text-2xl font-bold text-gray-800">Businesses Management</h1>
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
                    <!-- Page Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-building text-blue-600 mr-3"></i>
                                All Businesses
                            </h2>
                            <nav class="text-sm mt-2">
                                <ol class="flex list-none p-0">
                                    <li class="flex items-center">
                                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 hover:underline">Dashboard</a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    </li>
                                    <li class="text-gray-600 font-medium">Businesses</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="business_register.php" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span class="font-medium">Register New Business</span>
                        </a>
                    </div>
                    
                    <!-- Alert Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500 text-xl"></i>
                                <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-red-500 text-xl"></i>
                                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-100 text-sm font-medium uppercase">Total Businesses</p>
                                    <h3 class="text-3xl font-bold mt-2"><?php echo count($businesses); ?></h3>
                                </div>
                                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-building text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100 text-sm font-medium uppercase">Active Businesses</p>
                                    <h3 class="text-3xl font-bold mt-2">
                                        <?php echo count(array_filter($businesses, function($b) { return $b['status'] == 'active'; })); ?>
                                    </h3>
                                </div>
                                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-orange-100 text-sm font-medium uppercase">Inactive Businesses</p>
                                    <h3 class="text-3xl font-bold mt-2">
                                        <?php echo count(array_filter($businesses, function($b) { return $b['status'] != 'active'; })); ?>
                                    </h3>
                                </div>
                                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-times-circle text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Businesses Table -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <!-- Table Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-6 py-4">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <h5 class="text-lg font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-list mr-2 text-blue-600"></i>
                                    Registered Businesses
                                </h5>
                                <div class="w-full sm:w-auto">
                                    <div class="relative">
                                        <input type="text" 
                                               id="searchInput" 
                                               class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                               placeholder="Search businesses...">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Table Container -->
                        <div class="overflow-x-auto">
                            <?php if (count($businesses) > 0): ?>
                                <table class="min-w-full divide-y divide-gray-200" id="businessesTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Business Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Registration #</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contact</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reg. Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($businesses as $business): ?>
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                            <i class="fas fa-building text-blue-600"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                <?php echo htmlspecialchars($business['business_name']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <?php echo htmlspecialchars($business['registration_number']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-briefcase mr-1 text-xs"></i>
                                                        <?php echo htmlspecialchars($business['business_type']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        <div class="flex items-center mb-1">
                                                            <i class="fas fa-envelope text-gray-400 mr-2 text-xs"></i>
                                                            <span><?php echo htmlspecialchars($business['email']); ?></span>
                                                        </div>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-phone text-gray-400 mr-2 text-xs"></i>
                                                            <span><?php echo htmlspecialchars($business['phone']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                                    <?php echo date('M d, Y', strtotime($business['registration_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if ($business['status'] == 'active'): ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                            <span class="w-2 h-2 mr-1.5 rounded-full bg-green-500"></span>
                                                            Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                            <span class="w-2 h-2 mr-1.5 rounded-full bg-red-500"></span>
                                                            Inactive
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <a href="business_view.php?id=<?php echo $business['id']; ?>" 
                                                           class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" 
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="business_edit.php?id=<?php echo $business['id']; ?>" 
                                                           class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-all" 
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="business_delete.php?id=<?php echo $business['id']; ?>" 
                                                           class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                                                           title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this business? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-building text-3xl text-gray-400"></i>
                                    </div>
                                    <p class="text-lg font-medium text-gray-500">No businesses registered yet</p>
                                    <p class="text-sm text-gray-400 mt-1">Get started by registering your first business</p>
                                    <a href="business_register.php" class="inline-flex items-center mt-4 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        Register First Business
                                    </a>
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
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('#businessesTable tbody tr');
            
            if (searchInput && tableRows.length > 0) {
                searchInput.addEventListener('keyup', function() {
                    const searchValue = this.value.toLowerCase().trim();
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchValue) ? '' : 'none';
                    });
                    
                    // Check if any rows are visible
                    const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
                    
                    // Show "no results" message if search returns nothing
                    if (visibleRows.length === 0 && searchValue !== '') {
                        const tbody = document.querySelector('#businessesTable tbody');
                        const noResultsRow = tbody.querySelector('.no-results-row');
                        
                        if (!noResultsRow) {
                            const row = tbody.insertRow(0);
                            row.className = 'no-results-row';
                            row.innerHTML = `
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-search text-5xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium">No results found</p>
                                        <p class="text-sm mt-1">Try adjusting your search terms</p>
                                    </div>
                                </td>
                            `;
                        }
                    } else {
                        const noResultsRow = document.querySelector('.no-results-row');
                        if (noResultsRow) noResultsRow.remove();
                    }
                });
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
            
            // Add animation to table rows
            const rows = document.querySelectorAll('#businessesTable tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>