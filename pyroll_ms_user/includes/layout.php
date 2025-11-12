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

        /* Dark mode overrides */
        .dark body { background-color: #0b1220; color: #e2e8f0; }
        .dark .bg-white { background-color: #0f172a !important; }
        .dark .bg-slate-50, .dark .bg-slate-50\/50 { background-color: #0b1220 !important; }
        .dark .text-slate-800 { color: #e2e8f0 !important; }
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .text-slate-600 { color: #94a3b8 !important; }
        .dark .text-slate-500 { color: #7c8ca2 !important; }
        /* Dashboard summary card: ensure "This month's payroll" label is readable */
        .dark .bg-gradient-to-br.from-white.to-emerald-50 .text-slate-500 { color: #cbd5e1 !important; }
        .dark .text-gray-900 { color: #f1f5f9 !important; }
        .dark .text-gray-800 { color: #e2e8f0 !important; }
        .dark .text-gray-700 { color: #cbd5e1 !important; }
        .dark .text-gray-600 { color: #94a3b8 !important; }
        .dark .text-gray-500 { color: #7c8ca2 !important; }
        .dark .text-primary-900 { color: #93c5fd !important; }
        .dark .text-primary-800 { color: #7dd3fc !important; }
        .dark .text-primary-700 { color: #60a5fa !important; }
        .dark .text-primary-600 { color: #38bdf8 !important; }
        .dark .border-slate-200 { border-color: #334155 !important; }
        .dark .border-slate-300 { border-color: #475569 !important; }
        .dark .border-gray-300 { border-color: #475569 !important; }
        .dark .border-slate-100 { border-color: #334155 !important; }
        .dark .border-purple-200 { border-color: #7c3aed !important; }
        .dark .border-primary-200 { border-color: #2563eb !important; }
        .dark .hover\:bg-slate-100:hover { background-color: #1e293b !important; }
        .dark .hover\:bg-slate-50:hover { background-color: #0f172a !important; }
        .dark .hover\:bg-slate-50:hover .text-slate-800,
        .dark .hover\:bg-slate-50:hover .text-slate-700 { color: #e2e8f0 !important; }
        .dark .hover\:bg-slate-50:hover .text-slate-600,
        .dark .hover\:bg-slate-50:hover .text-slate-500 { color: #cbd5e1 !important; }
        .dark .hover\:bg-purple-50:hover { background-color: #1f1536 !important; }
        .dark .hover\:bg-primary-50:hover { background-color: #0b1324 !important; }
        /* Quick Actions: ensure text remains visible on hover for first two cards */
        .dark .hover\:bg-blue-50:hover { background-color: #0b1220 !important; }
        .dark .hover\:bg-emerald-50:hover { background-color: #0c1f1a !important; }
        .dark .hover\:bg-blue-50:hover .text-slate-800,
        .dark .hover\:bg-blue-50:hover .text-slate-700 { color: #e2e8f0 !important; }
        .dark .hover\:bg-blue-50:hover .text-slate-600,
        .dark .hover\:bg-blue-50:hover .text-slate-500 { color: #cbd5e1 !important; }
        .dark .hover\:bg-emerald-50:hover .text-slate-800,
        .dark .hover\:bg-emerald-50:hover .text-slate-700 { color: #e2e8f0 !important; }
        .dark .hover\:bg-emerald-50:hover .text-slate-600,
        .dark .hover\:bg-emerald-50:hover .text-slate-500 { color: #cbd5e1 !important; }

        /* Notifications dropdown */
        .notif-dropdown { backdrop-filter: saturate(120%) blur(2px); }
        .notif-item { border-bottom: 1px solid rgba(148, 163, 184, 0.25); }
        .notif-item:last-child { border-bottom: none; }
        .dark .notif-dropdown { background-color: #0f172a !important; border-color: #334155 !important; }
        .dark .notif-item { border-color: #334155 !important; }
        .dark .notif-item:hover { background-color: #1e293b !important; }
        /* Toastify fade-out over 5 seconds */
        @keyframes toastLifeFade {
            0% { opacity: 1; }
            92% { opacity: 1; }
            100% { opacity: 0; }
        }
        .toast-animated.toastify {
            animation: toastLifeFade 5s ease-out forwards;
        }
        .dark .shadow-smooth { box-shadow: 0 4px 12px rgba(0,0,0,0.6) !important; }
        .dark .nav-gradient { background: linear-gradient(90deg, #0a1726 0%, #0b2034 100%); }
        .dark .sidebar-gradient { background: linear-gradient(180deg, #0b2034 0%, #0a1726 50%, #09121f 100%); }

        /* Tables in dark mode */
        .dark table { color: #e2e8f0 !important; background-color: #0f172a !important; }
        .dark thead th {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-bottom-color: #334155 !important;
        }
        .dark tbody td { border-color: #334155 !important; }
        .dark tbody tr:nth-child(odd) { background-color: #0b1324 !important; }
        .dark tbody tr:nth-child(even) { background-color: #101a30 !important; }
        .dark tbody tr:hover { background-color: #1e293b !important; }

        /* Common gray/slate backgrounds for cards in dark mode */
        .dark .bg-gray-50, .dark .bg-gray-100 { background-color: #0b1220 !important; }
        .dark .bg-gray-200 { background-color: #111827 !important; }
        .dark .border-gray-200 { border-color: #334155 !important; }
        .dark .bg-slate-100 { background-color: #0f172a !important; }
        .dark .bg-slate-200 { background-color: #111827 !important; }
        .dark .bg-primary-50 { background-color: #0b1324 !important; }

        /* Generic card containers */
        .dark .card,
        .dark .panel,
        .dark .stat-card,
        .dark .widget,
        .dark .box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        /* Dashboard gradient cards: ensure dark gradients */
        .dark .bg-gradient-to-br.from-white.to-slate-50 {
            background-image: linear-gradient(135deg, #0f172a 0%, #0b1220 100%) !important;
        }
        /* Tailwind gradient stop overrides for dark mode */
        .dark .from-white { --tw-gradient-from: #0f172a !important; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(15, 23, 42, 0)) !important; }
        .dark .to-slate-50 { --tw-gradient-to: #0b1220 !important; }
        .dark .from-slate-50 { --tw-gradient-from: #0b1220 !important; }
        .dark .to-white { --tw-gradient-to: #0f172a !important; }
        .dark .from-primary-50 { --tw-gradient-from: #0b1324 !important; }
        .dark .to-blue-50 { --tw-gradient-to: #0b1324 !important; }

        /* Accent badges on dashboard cards */
        .dark .bg-emerald-100 { background-color: #064e3b !important; }
        .dark .text-emerald-600 { color: #6ee7b7 !important; }
        .dark .bg-violet-100 { background-color: #3b0764 !important; }
        .dark .text-violet-600 { color: #c4b5fd !important; }
        .dark .bg-purple-100 { background-color: #3b0764 !important; }
        .dark .text-purple-600 { color: #c4b5fd !important; }
        .dark .hover\:bg-purple-50:hover { background-color: #1f1536 !important; }

        /* Ensure card headings and subtle text remain readable */
        .dark .card h1, .dark .panel h1, .dark .stat-card h1,
        .dark .card h2, .dark .panel h2, .dark .stat-card h2,
        .dark .card h3, .dark .panel h3, .dark .stat-card h3,
        .dark .card h4, .dark .panel h4, .dark .stat-card h4 {
            color: #e2e8f0 !important;
        }
        .dark .card .text-muted, .dark .panel .text-muted, .dark .stat-card .text-muted {
            color: #94a3b8 !important;
        }

        /* Form controls in dark mode */
        .dark input[type="text"],
        .dark input[type="email"],
        .dark input[type="password"],
        .dark input[type="number"],
        .dark input[type="date"],
        .dark input[type="time"],
        .dark input[type="month"],
        .dark input[type="url"],
        .dark input[type="tel"],
        .dark input[type="search"],
        .dark select,
        .dark textarea,
        .dark .form-control,
        .dark .form-input {
            background-color: #0f172a !important; /* slate-900 */
            color: #e2e8f0 !important;            /* slate-200 */
            border-color: #334155 !important;     /* slate-700 */
        }

        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #94a3b8 !important; /* slate-400 */
        }

        .dark input:focus,
        .dark select:focus,
        .dark textarea:focus,
        .dark .form-control:focus,
        .dark .form-input:focus {
            border-color: #2563eb !important; /* blue-600 */
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.35) !important; /* focus ring */
            outline: none !important;
        }

        .dark label,
        .dark .form-label {
            color: #e2e8f0 !important;
        }

        .dark input[disabled],
        .dark select[disabled],
        .dark textarea[disabled] {
            background-color: #111827 !important; /* gray-900 */
            color: #9ca3af !important;             /* gray-400 */
            border-color: #374151 !important;      /* gray-700 */
        }
        .dark .menu-item-active { background: rgba(255,255,255,0.06); box-shadow: 0 0 15px rgba(14,165,233,0.15); }
        .dark .search-input { background-color: #0b1220; color: #e2e8f0; border-color: #334155; }
        .dark .table-row-hover:hover { background-color: #0f172a; }

        /* Mobile compact styles: cards, forms, tables, and footer */
        @media (max-width: 640px) {
            /* Cards: reduce padding and heading sizes */
            #content .p-6 { padding: 1rem !important; }
            #content .p-4 { padding: 0.75rem !important; }
            #content .text-xl { font-size: 1.125rem !important; line-height: 1.5rem !important; }
            #content .text-lg { font-size: 1rem !important; line-height: 1.375rem !important; }

            /* Forms: tighter inputs and labels */
            #content .form-input,
            #content input,
            #content select,
            #content textarea { padding: 0.5rem !important; font-size: 0.95rem !important; }
            #content label { font-size: 0.9rem !important; }

            /* Tables: smaller font and cell padding, wrap long text */
            #content table,
            #content thead th,
            #content tbody td { font-size: 0.875rem !important; }
            #content thead th,
            #content tbody td { padding: 0.5rem !important; white-space: normal; word-break: break-word; }
            #content .table-container { overflow-x: auto; }

            /* Footer: larger padding to span and feel substantial */
            #pageFooter { padding-top: 1.25rem; padding-bottom: 1.25rem; }
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen font-inter">
    <div class="flex h-screen overflow-hidden">
        <!-- Enhanced Sidebar - Fixed Position -->
        <div id="sidebar" class="sidebar-gradient text-white sidebar-transition fixed left-0 top-0 h-screen w-64 md:w-64 overflow-y-auto shadow-xl z-30 transform -translate-x-full md:translate-x-0">
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
                        <a href="dashboard.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'dashboard.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-tachometer-alt text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Dashboard</span>
                            <?php if ($current_page == 'dashboard.php'): ?>
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
                        <a href="deductions.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'deductions.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-percent text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Deductions</span>
                            <?php if ($current_page == 'deductions.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="slips.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'slips.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-file-invoice-dollar text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Salary Slips</span>
                            <?php if ($current_page == 'slips.php'): ?>
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
                    
                    <li>
                        <a href="profile.php" class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-white/10 hover:shadow-lg hover-lift <?php echo $current_page == 'profile.php' ? 'menu-item-active text-white font-semibold' : 'text-slate-200'; ?>">
                            <i class="fas fa-user text-lg w-6 text-center flex-shrink-0"></i>
                            <span class="menu-text font-medium ml-3">Profile</span>
                            <?php if ($current_page == 'profile.php'): ?>
                                <span class="ml-auto w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
            </div>

            <!-- Sidebar footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-600/30">
                <div class="text-center text-slate-300 text-sm">
                    <!-- <p>v2.1.0</p> -->
                    <p class="text-xs mt-4">© <?= date('Y'); ?> Payroll MS</p>
                </div>
            </div>
        </div>
        <!-- Mobile sidebar backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-20 md:hidden"></div>

        <!-- Main Content Area - With proper margin -->
        <div id="content" class="flex-1 content-transition md:ml-64 ml-0 flex flex-col">
            <!-- Enhanced Navbar -->
            <nav class="bg-white shadow-smooth border-b border-slate-200 py-3 px-4 md:py-4 md:px-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button id="mobileMenuButton" class="md:hidden p-2 mr-2 text-slate-600 hover:text-primary-600 rounded-lg hover:bg-slate-100 transition-colors duration-200" aria-label="Open menu" title="Open menu">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
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
                        <div class="relative">
                            <button id="notifButton" class="relative p-2 text-slate-600 hover:text-primary-600 rounded-lg hover:bg-slate-100 transition-colors duration-200" aria-haspopup="true" aria-expanded="false" title="Notifications">
                                <i class="fas fa-bell text-lg"></i>
                                <span id="notifBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden">0</span>
                            </button>
                            <div id="notifDropdown" class="notif-dropdown absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-smooth border border-slate-200 hidden z-20">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                                    <span class="text-slate-800 font-semibold">Notifications</span>
                                    <a href="#" id="notifMarkAll" class="text-primary-600 hover:text-primary-700 text-sm">Mark all read</a>
                                </div>
                                <div id="notifList" class="max-h-80 overflow-auto py-2">
                                    <div class="px-4 py-6 text-slate-500 text-sm">Loading…</div>
                                </div>
                                <div class="px-4 py-3 border-t border-slate-200">
                                    <a href="payroll.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium inline-flex items-center">View all
                                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle -->
                        <button id="themeToggle" class="p-2 text-slate-600 hover:text-primary-600 rounded-lg hover:bg-slate-100 transition-colors duration-200" title="Toggle dark/light">
                            <i id="themeIcon" class="fas fa-moon text-lg"></i>
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
            <div class="flex-1 p-6 overflow-y-auto bg-slate-50/50 pb-24">
                <!-- Content will be injected here -->
                <?php if (isset($page_content)) echo $page_content; ?>
                <!-- Full-width page footer -->
                <footer id="pageFooter" class="mt-6 w-full bg-white border-t border-slate-200 text-slate-600 text-sm flex items-center justify-center px-4 py-3 md:hidden">
                    <p class="text-xs">© <?php echo date('Y'); ?> Payroll MS</p>
                </footer>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('notifButton');
        var dd = document.getElementById('notifDropdown');
        var badge = document.getElementById('notifBadge');
        var list = document.getElementById('notifList');
        var markAll = document.getElementById('notifMarkAll');

        function renderItems(items) {
            if (!items || !items.length) {
                list.innerHTML = '<div class="px-4 py-6 text-slate-500 text-sm">No notifications</div>';
                return;
            }
            list.innerHTML = items.map(function (item) {
                var amount = item.amount ? ('UGX ' + item.amount) : '';
                return (
                    '<a href="' + (item.url || 'payroll.php') + '" class="block notif-item px-4 py-3 hover:bg-slate-50 transition-colors duration-150">'
                    + '<div class="flex items-start justify-between">'
                    +   '<div>'
                    +     '<p class="text-slate-800 font-medium">' + (item.title || 'Update') + '</p>'
                    +     '<p class="text-slate-500 text-sm">' + (item.message || '') + '</p>'
                    +   '</div>'
                    +   '<div class="text-right">'
                    +     (amount ? ('<p class="text-slate-800 font-semibold">' + amount + '</p>') : '')
                    +     '<p class="text-slate-500 text-xs">' + (item.created_at || '') + '</p>'
                    +   '</div>'
                    + '</div>'
                    + '</a>'
                );
            }).join('');
        }

        function fetchNotifications() {
            fetch('includes/notifications.php')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var count = (data && typeof data.count === 'number') ? data.count : (data.items ? data.items.length : 0);
                    if (count > 0) {
                        badge.textContent = String(count);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                    renderItems((data && data.items) ? data.items : []);
                })
                .catch(function () {
                    list.innerHTML = '<div class="px-4 py-6 text-slate-500 text-sm">Unable to load notifications</div>';
                });
        }

        function toggleDropdown() {
            if (dd.classList.contains('hidden')) {
                dd.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                fetchNotifications();
            } else {
                dd.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        if (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleDropdown();
            });
        }

        document.addEventListener('click', function () {
            if (!dd.classList.contains('hidden')) {
                dd.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !dd.classList.contains('hidden')) {
                dd.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }
        });

        if (markAll) {
            markAll.addEventListener('click', function (e) {
                e.preventDefault();
                badge.classList.add('hidden');
                var items = list.querySelectorAll('.notif-item');
                items.forEach(function (el) { el.classList.add('opacity-60'); });
            });
        }
    });
    </script>

    <!-- JavaScript for sidebar toggle, theme toggle, and toast notifications -->
    <script>
        // Initialize saved theme early
        (function() {
            try {
                var saved = localStorage.getItem('theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (saved === 'light') {
                    document.documentElement.classList.remove('dark');
                }
            } catch(e) {}
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const sidebarTitle = document.getElementById('sidebarTitle');
            const fixedFooter = document.getElementById('fixedFooter');

            // Theme toggle
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const setIcon = () => {
                const isDark = document.documentElement.classList.contains('dark');
                if (isDark) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                } else {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
            };
            setIcon();
            if (themeToggle) {
                themeToggle.addEventListener('click', function(){
                    const isDark = document.documentElement.classList.toggle('dark');
                    try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e) {}
                    setIcon();
                });
            }

            // Show welcome toast if user just logged in
            <?php if (isset($_SESSION["show_welcome"]) && $_SESSION["show_welcome"] === true): ?>
                Toastify({
                    text: "Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)",
                    stopOnFocus: true,
                    className: "rounded-xl shadow-lg font-inter toast-animated",
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
                    if (fixedFooter) {
                        fixedFooter.classList.remove('ml-64');
                        fixedFooter.classList.add('ml-20');
                    }
                    
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
                    if (fixedFooter) {
                        fixedFooter.classList.remove('ml-20');
                        fixedFooter.classList.add('ml-64');
                    }
                    
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
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            const isMobile = () => window.innerWidth < 768;

            function openMobileSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeMobileSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                if (sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            function toggleSidebarFunc() {
                if (isMobile()) {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        openMobileSidebar();
                    } else {
                        closeMobileSidebar();
                    }
                    return;
                }

                const menuTexts = document.querySelectorAll('.menu-text');
                const icons = sidebar.querySelectorAll('a i');
                
                if (sidebar.classList.contains('w-64')) {
                    // Minimize sidebar (desktop)
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    content.classList.remove('md:ml-64');
                    content.classList.add('md:ml-20');
                    if (fixedFooter) {
                        fixedFooter.classList.remove('ml-64');
                        fixedFooter.classList.add('ml-20');
                    }
                    menuTexts.forEach(text => text.classList.add('hidden'));
                    sidebarTitle.classList.add('hidden');
                    const menuLinks = sidebar.querySelectorAll('.mt-6 a');
                    menuLinks.forEach(link => { link.classList.add('justify-center'); });
                } else {
                    // Maximize sidebar (desktop)
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    content.classList.remove('md:ml-20');
                    content.classList.add('md:ml-64');
                    if (fixedFooter) {
                        fixedFooter.classList.remove('ml-20');
                        fixedFooter.classList.add('ml-64');
                    }
                    menuTexts.forEach(text => text.classList.remove('hidden'));
                    sidebarTitle.classList.remove('hidden');
                    const menuLinks = sidebar.querySelectorAll('.mt-6 a');
                    menuLinks.forEach(link => { link.classList.remove('justify-center'); });
                }
            }

            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', toggleSidebarFunc);
            }
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', openMobileSidebar);
            }
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeMobileSidebar);
            }
            window.addEventListener('resize', function() {
                if (!isMobile()) {
                    closeMobileSidebar();
                }
            });
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
                duration: 5000,
                gravity: "top",
                position: "right",
                backgroundColor: bgColors[type],
                stopOnFocus: true,
                className: "rounded-xl shadow-lg font-inter toast-animated",
                style: {
                    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
                }
            }).showToast();
        }

        // Initialize table searches and sortable headers on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Setup table search
            const searchContainers = document.querySelectorAll('.table-search-container');
            searchContainers.forEach(container => {
                const searchInput = container.querySelector('input');
                const tableId = searchInput.getAttribute('data-table-id');
                if (tableId) {
                    setupTableSearch(tableId, searchInput.id);
                }
            });

            // Setup sortable tables
            document.querySelectorAll('table.sortable-table').forEach(table => {
                const tbody = table.querySelector('tbody');
                const ths = table.querySelectorAll('thead .sortable-th');
                if (!tbody || ths.length === 0) return;

                ths.forEach((th, idx) => {
                    th.style.userSelect = 'none';
                    th.addEventListener('click', () => {
                        const type = th.dataset.type || 'string';
                        const current = th.dataset.order || 'none';
                        const nextOrder = current === 'asc' ? 'desc' : 'asc';
                        ths.forEach(t => t.dataset.order = 'none');
                        th.dataset.order = nextOrder;

                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const getVal = (td) => td.getAttribute('data-sort-value') ?? td.textContent.trim();
                        const parseVal = (val) => {
                            if (type === 'number') {
                                const num = parseFloat(String(val).replace(/[^0-9.\-]/g, ''));
                                return isNaN(num) ? -Infinity : num;
                            } else if (type === 'date') {
                                const ts = parseInt(val, 10);
                                if (!isNaN(ts)) return ts;
                                const d = new Date(val);
                                return isNaN(d.getTime()) ? 0 : d.getTime();
                            } else {
                                return String(val).toLowerCase();
                            }
                        };
                        rows.sort((a, b) => {
                            const aVal = parseVal(getVal(a.children[idx]));
                            const bVal = parseVal(getVal(b.children[idx]));
                            if (aVal < bVal) return nextOrder === 'asc' ? -1 : 1;
                            if (aVal > bVal) return nextOrder === 'asc' ? 1 : -1;
                            return 0;
                        });
                        rows.forEach(r => tbody.appendChild(r));
                    });
                });
            });
        });
    </script>
    <!-- Fixed Footer anchored to viewport bottom -->
    <div id="fixedFooter" class="fixed bottom-0 left-0 right-0 ml-64 bg-white border-t border-slate-200 shadow-card z-20">
        <div class="px-6 py-3 flex items-center justify-between overflow-x-auto">
            <div class="text-xs md:text-sm text-slate-500">
                <span class="font-medium text-slate-700">© <?php echo date('Y'); ?> Payroll MS</span>
                <span class="ml-2 hidden sm:inline">All Rights Reserved</span>
            </div>
            <div class="flex items-center space-x-3 text-sm whitespace-nowrap">
                <a href="dashboard.php" class="text-slate-600 hover:text-primary-600">Dashboard</a>
                <span class="text-slate-300">|</span>
                <a href="employees.php" class="text-slate-600 hover:text-primary-600">Employees</a>
                <span class="text-slate-300">|</span>
                <a href="payroll.php" class="text-slate-600 hover:text-primary-600">Payroll</a>
                <span class="text-slate-300">|</span>
                <a href="reports.php" class="text-slate-600 hover:text-primary-600">Reports</a>
                 <span class="text-slate-300">|</span>
                <a href="profile.php" class="text-slate-600 hover:text-primary-600">Profile</a>
                 <span class="text-slate-300">|</span>
                <a href="slips.php" class="text-slate-600 hover:text-primary-600">Slips</a>
            </div>
        </div>
    </div>

</body>

</html>