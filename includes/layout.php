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
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'roboto': ['"Roboto Condensed"', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#caf0f8',  /* Lightest blue */
                            100: '#90e0ef', /* Light blue */
                            200: '#00b4d8', /* Medium blue */
                            300: '#0077b6', /* Dark blue */
                            400: '#00b4d8', /* Medium blue */
                            500: '#0096c7', /* Polished blue */
                            600: '#0077b6', /* Dark blue */
                            700: '#023e8a', /* Deeper blue */
                            800: '#03045e', /* Very dark blue */
                            900: '#050A30', /* Deepest blue */
                        }
                    },
                    boxShadow: {
                        'blue-glow': '0 0 15px rgba(0, 180, 216, 0.5)',
                        'blue-sm': '0 1px 2px 0 rgba(0, 119, 182, 0.05)'
                    },
                    backgroundImage: {
                        'blue-gradient': 'linear-gradient(to right, #0077b6, #00b4d8)',
                        'blue-gradient-hover': 'linear-gradient(to right, #023e8a, #0077b6)'
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
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #00b4d8; /* Medium blue */
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0077b6; /* Dark blue */
        }
        
        /* Enhanced UI elements */
        .btn-primary {
            background-image: linear-gradient(to right, #0077b6, #00b4d8);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-image: linear-gradient(to right, #023e8a, #0077b6);
            box-shadow: 0 0 15px rgba(0, 180, 216, 0.5);
        }
        
        /* Transition for sidebar */
        .sidebar-transition {
            transition: width 0.3s ease-in-out;
        }
        
        /* Transition for main content */
        .content-transition {
            transition: margin-left 0.3s ease-in-out;
        }
        
        /* Table search styles */
        .table-search-container {
            margin-bottom: 1rem;
        }
        
        .table-search-input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            width: 100%;
            max-width: 300px;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .table-search-input:focus {
            border-color: #ffa726;
            box-shadow: 0 0 0 2px rgba(255, 167, 38, 0.2);
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen font-roboto">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-blue-gradient text-white sidebar-transition w-64 min-h-screen overflow-y-auto shadow-blue-glow">
            <div class="p-4 flex items-center justify-between">
                <h2 class="text-xl font-bold"><span class="full-title">Payroll MS</span><span class="short-title hidden">PMS</span></h2>
                <button id="toggleSidebar" class="text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="mt-4">
                <ul>
                    <li class="mb-1">
                        <a href="index.php" class="block py-2 px-4 <?php echo $current_page == 'index.php' ? 'bg-primary-100 text-primary-700' : 'hover:bg-primary-50 hover:text-primary-600'; ?> rounded transition duration-200 hover:shadow-blue-sm text-left">
                            <i class="fas fa-tachometer-alt mr-2 menu-icon"></i> <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="employees.php" class="block py-2 px-4 <?php echo $current_page == 'employees.php' ? 'bg-primary-100 text-primary-700' : 'hover:bg-primary-50 hover:text-primary-600'; ?> rounded transition duration-200 hover:shadow-blue-sm text-left">
                            <i class="fas fa-users mr-2 menu-icon"></i> <span class="menu-text">Employees</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="payroll.php" class="block py-2 px-4 <?php echo $current_page == 'payroll.php' ? 'bg-primary-100 text-primary-700' : 'hover:bg-primary-50 hover:text-primary-600'; ?> rounded transition duration-200 hover:shadow-blue-sm text-left">
                            <i class="fas fa-money-bill-wave mr-2 menu-icon"></i> <span class="menu-text">Payroll</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="reports.php" class="block py-2 px-4 <?php echo $current_page == 'reports.php' ? 'bg-primary-100 text-primary-700' : 'hover:bg-primary-50 hover:text-primary-600'; ?> rounded transition duration-200 hover:shadow-blue-sm text-left">
                            <i class="fas fa-chart-bar mr-2 menu-icon"></i> <span class="menu-text">Reports</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div id="content" class="flex-1 content-transition overflow-x-hidden">
            <!-- Navbar -->
            <nav class="bg-white shadow-md p-4 flex justify-between items-center">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="text-gray-600 focus:outline-none mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-primary-800">Payroll Management System</h1>
                </div>
                <div class="flex items-center">
                    <div class="mr-4 text-gray-700">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    </div>
                    <a href="auth/logout.php" class="bg-primary-600 hover:bg-primary-700 text-white py-1 px-3 rounded transition duration-200">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="p-6">
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
            const sidebarToggle = document.getElementById('sidebarToggle');
            const toggleSidebar = document.getElementById('toggleSidebar');
            
            // Show welcome toast if user just logged in
            <?php if (isset($_SESSION["show_welcome"]) && $_SESSION["show_welcome"] === true): ?>
            Toastify({
                text: "Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?> to PMS",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "linear-gradient(to right, #0077b6, #00b4d8)",
                stopOnFocus: true,
                className: "rounded shadow-blue-glow"
            }).showToast();
            <?php 
                // Reset the show_welcome flag
                $_SESSION["show_welcome"] = false;
            endif; ?>
            
            // Function to toggle sidebar
            function toggleSidebarFunc() {
                if (sidebar.classList.contains('w-64')) {
                    // Collapse sidebar
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    content.classList.add('ml-16');
                    
                    // Show only icons
                    document.querySelectorAll('.menu-text').forEach(item => {
                        item.classList.add('hidden');
                    });
                    document.querySelectorAll('.menu-icon').forEach(item => {
                        item.classList.remove('mr-2');
                    });
                    
                    // Switch to short title
                    document.querySelector('.full-title').classList.add('hidden');
                    document.querySelector('.short-title').classList.remove('hidden');
                    
                    // Hide text in sidebar links
                    const sidebarLinks = sidebar.querySelectorAll('a');
                    sidebarLinks.forEach(link => {
                        link.classList.add('text-center');
                        link.querySelectorAll('span').forEach(span => span.classList.add('hidden'));
                    });
                } else {
                    // Expand sidebar
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    content.classList.remove('ml-16');
                    
                    // Show text again
                    document.querySelectorAll('.menu-text').forEach(item => {
                        item.classList.remove('hidden');
                    });
                    document.querySelectorAll('.menu-icon').forEach(item => {
                        item.classList.add('mr-2');
                    });
                    
                    // Switch back to full title
                    document.querySelector('.full-title').classList.remove('hidden');
                    document.querySelector('.short-title').classList.add('hidden');
                    
                    // Show text in sidebar links
                    const sidebarLinks = sidebar.querySelectorAll('a');
                    sidebarLinks.forEach(link => {
                        link.classList.remove('text-center');
                        link.querySelectorAll('span').forEach(span => span.classList.remove('hidden'));
                    });
                }
            }
            
            // Event listeners for sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebarFunc);
            }
            
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', toggleSidebarFunc);
            }
            
            // Handle responsive behavior
            function handleResize() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-0');
                    content.classList.remove('ml-64');
                } else {
                    sidebar.classList.remove('w-0');
                    sidebar.classList.add('w-64');
                }
            }
            
            // Initial check on page load
            handleResize();
            
            // Listen for window resize
            window.addEventListener('resize', handleResize);
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
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        // Toast notification function
        function showToast(message, type = 'success') {
            const bgColors = {
                success: 'linear-gradient(to right, #00b4d8, #0077b6)',
                error: 'linear-gradient(to right, #ef476f, #d90429)',
                info: 'linear-gradient(to right, #90e0ef, #00b4d8)',
                warning: 'linear-gradient(to right, #ffd166, #ffc300)'
            };
            
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: bgColors[type],
                stopOnFocus: true,
                className: "font-roboto"
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
            
            // Show welcome toast
            // setTimeout(() => {
            //     showToast('Welcome to Payroll Management System', 'info');
            // }, 1000);
        });
    </script>
</body>
</html>