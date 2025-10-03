<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth/login.php");
    exit;
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif']
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
                        },
                        accent: {
                            50: '#fefce8',
                            100: '#fef9c3',
                            500: '#eab308',
                            600: '#ca8a04',
                            700: '#a16207'
                        }
                    },
                    boxShadow: {
                        'smooth': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'glow': '0 0 20px rgba(14, 165, 233, 0.15)',
                        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Toast notification library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Enhanced UI elements */
        .sidebar-gradient {
            background: linear-gradient(180deg, #0c4a6e 0%, #075985 50%, #0369a1 100%);
        }

        .nav-gradient {
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Smooth transitions */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-transition {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Active menu item glow */
        .menu-item-active {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.3);
            border-left: 4px solid #0ea5e9;
        }

        /* Hover effects */
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
        }

        /* Table improvements */
        .table-row-hover:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        /* Search input styling */
        .search-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z' clip-rule='evenodd' /%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 16px;
            padding-left: 40px;
        }

        /* Loading animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen font-inter">
    <div class="flex h-screen overflow-hidden">
        <!-- Enhanced Sidebar - Fixed Position -->
        <div id="sidebar" class="sidebar-gradient text-white sidebar-transition fixed left-0 top-0 h-screen w-64 overflow-y-auto shadow-xl z-30">
            <!-- Sidebar header with logo -->
            <div class="p-6 flex items-center justify-between border-b border-slate-600/30">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-money-check-alt text-primary-600 text-lg"></i>
                    </div>
                    <div id="sidebarTitle">
                        <h2 class="text-xl font-bold">Payroll MS</h2>
                        <p class="text-slate-300 text-xs">Professional Edition</p>
                    </div>
                </div>
                <button id="toggleSidebar" class="text-slate-300 hover:text-white focus:outline-none transition-colors duration-200">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
            
            <!-- Navigation Menu -->
            <div class="mt-6 px-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'index.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-tachometer-alt text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Dashboard</span>
                            <?php if ($current_page == 'index.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="employees.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'employees.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-users text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Employees</span>
                            <?php if ($current_page == 'employees.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="payroll.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'payroll.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-money-bill-wave text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Payroll</span>
                            <?php if ($current_page == 'payroll.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="reports.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'reports.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-chart-bar text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Reports</span>
                            <?php if ($current_page == 'reports.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-600/30">
                <div class="text-center text-slate-300 text-sm">
                    <p>v2.1.0</p>
                    <p class="text-xs mt-1">© 2024 Payroll MS</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area - With proper margin -->
        <div id="content" class="flex-1 content-transition ml-64 flex flex-col">
            <!-- Enhanced Navbar -->
            <nav class="bg-white shadow-smooth border-b border-slate-200 py-4 px-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold text-primary-900 flex items-center space-x-3">
                                <span class="uppercase tracking-tight"><?= htmlspecialchars($_SESSION['business_name']); ?></span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-600 text-lg font-normal"><?= htmlspecialchars($_SESSION['business_address']); ?></span>
                            </h1>
                            <p class="text-slate-500 text-sm mt-1">Payroll Management System</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-slate-600 hover:text-primary-600 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                        
                        <!-- User Profile -->
                        <div class="flex items-center space-x-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            <div class="hidden md:block">
                                <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($_SESSION['business_role']); ?></p>
                            </div>
                        </div>
                        
                        <!-- Logout Button -->
                        <a href="./auth/logout.php" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-md hover:shadow-lg hover-lift font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="flex-1 p-6 overflow-y-auto bg-slate-50/50">
                <!-- Content will be injected here -->
                <?php if (isset($page_content)) echo $page_content; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript for sidebar toggle and toast notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const sidebarTitle = document.getElementById('sidebarTitle');

            // Show welcome toast if user just logged in
            <?php if (isset($_SESSION["show_welcome"]) && $_SESSION["show_welcome"] === true): ?>
                Toastify({
                    text: "Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)",
                    stopOnFocus: true,
                    className: "rounded-xl shadow-lg font-inter",
                    style: {
                        boxShadow: '0 4px 12px rgba(14, 165, 233, 0.3)'
                    }
                }).showToast();
            <?php
                // Reset the show_welcome flag
                $_SESSION["show_welcome"] = false;
            endif; ?>

            // Function to toggle sidebar
            function toggleSidebarFunc() {
                const menuTexts = document.querySelectorAll('.menu-text');
                const icons = sidebar.querySelectorAll('a i');
                
                if (sidebar.classList.contains('w-64')) {
                    // Minimize sidebar
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    content.classList.remove('ml-64');
                    content.classList.add('ml-20');
                    
                    // Hide text
                    menuTexts.forEach(text => text.classList.add('hidden'));
                    
                    // Hide title
                    sidebarTitle.classList.add('hidden');
                    
                    // Center icons
                    const menuLinks = sidebar.querySelectorAll('.mt-6 a');
                    menuLinks.forEach(link => {
                        link.classList.add('justify-center');
                    });
                } else {
                    // Maximize sidebar
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    content.classList.remove('ml-20');
                    content.classList.add('ml-64');
                    
                    // Show text
                    menuTexts.forEach(text => text.classList.remove('hidden'));
                    
                    // Show title
                    sidebarTitle.classList.remove('hidden');
                    
                    // Restore alignment
                    const menuLinks = sidebar.querySelectorAll('.mt-6 a');
                    menuLinks.forEach(link => {
                        link.classList.remove('justify-center');
                    });
                }
            }

            // Event listener for sidebar toggle
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', toggleSidebarFunc);
            }
        });
    </script>

    <!-- JavaScript for table search and toast notifications -->
    <script>
        // Table search functionality
        function setupTableSearch(tableId, searchInputId) {
            const table = document.getElementById(tableId);
            const searchInput = document.getElementById(searchInputId);

            if (!table || !searchInput) return;

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.classList.remove('hidden');
                        row.classList.add('table-row-hover');
                    } else {
                        row.classList.add('hidden');
                        row.classList.remove('table-row-hover');
                    }
                });
            });
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            const bgColors = {
                success: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                error: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                info: 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)',
                warning: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
            };

            Toastify({
                text: message,
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: bgColors[type],
                stopOnFocus: true,
                className: "rounded-xl shadow-lg font-inter",
                style: {
                    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
                }
            }).showToast();
        }

        // Initialize all table searches on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Find all tables with search functionality
            const searchContainers = document.querySelectorAll('.table-search-container');
            searchContainers.forEach(container => {
                const searchInput = container.querySelector('input');
                const tableId = searchInput.getAttribute('data-table-id');
                if (tableId) {
                    setupTableSearch(tableId, searchInput.id);
                }
            });
        });
    </script>
</body>

</html>