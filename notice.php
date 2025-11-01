<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "seatplan_management";


$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Start session
session_start();

// Fetch site settings
$settings = $conn->query("SELECT * FROM site_settings LIMIT 1")->fetch_assoc();

// Fetch all notices, most recent first
$notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC");

// Count total notices
$total_notices = $notices->num_rows;

// Check if we're viewing a specific notice
$current_notice = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notice_id = $conn->real_escape_string($_GET['id']);
    $notice_query = $conn->query("SELECT * FROM notices WHERE notice_id = '$notice_id'");
    if ($notice_query && $notice_query->num_rows > 0) {
        $current_notice = $notice_query->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_notice ? htmlspecialchars($current_notice['title']) . ' - ' : '' ?>Notices - <?= htmlspecialchars($settings['site_title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/906/906175.png">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Professional Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-link {
            position: relative;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link.active::after {
            width: 100%;
        }
        
        /* Mobile Menu Styles */
        .mobile-menu-container {
            position: fixed;
            top: 0;
            right: -100%;
            width: 320px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .mobile-menu-container.open {
            right: 0;
        }
        
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .mobile-menu-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        
        /* Login Dropdown */
        .login-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .login-group:hover .login-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Hamburger Animation */
        .hamburger-line {
            transition: all 0.3s ease;
        }
        
        .hamburger.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        
        .hamburger.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        
        /* Notice Styles */
        .notice-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .notice-card.important {
            border-left-color: #e53e3e;
            background: linear-gradient(to right, #fed7d7, #fff5f5);
        }
        
        .notice-card.general {
            border-left-color: #3182ce;
            background: linear-gradient(to right, #bee3f8, #ebf8ff);
        }
        
        .notice-card.exam {
            border-left-color: #38a169;
            background: linear-gradient(to right, #c6f6d5, #f0fff4);
        }
        
        .notice-card.other {
            border-left-color: #805ad5;
            background: linear-gradient(to right, #e9d8fd, #faf5ff);
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-important {
            background-color: #fed7d7;
            color: #c53030;
        }
        
        .badge-general {
            background-color: #bee3f8;
            color: #2b6cb0;
        }
        
        .badge-exam {
            background-color: #c6f6d5;
            color: #276749;
        }
        
        .badge-new {
            background-color: #fbb6ce;
            color: #b83280;
        }
        
        /* Floating Animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translate(0, 0px); }
            50% { transform: translate(0, -15px); }
            100% { transform: translate(0, 0px); }
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.25rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Professional Navbar -->
    <nav class="navbar fixed top-0 left-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-chair text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($settings['site_title']); ?></h1>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="index.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Home</a>
                    <a href="about.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">About</a>
                    <a href="find_seat.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Find Seat</a>
                    <a href="notice.php" class="nav-link text-indigo-600 active transition-colors duration-300">Notices</a>
                    <a href="department.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Departments</a>
                    <a href="contact.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Contact</a>
                    
                    <!-- Login Dropdown -->
                    <div class="login-group relative">
                        <button class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300 flex items-center space-x-1">
                            <span>Login</span>
                            <i class="fas fa-chevron-down text-xs mt-1"></i>
                        </button>
                        <div class="login-dropdown absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
                            <a href="login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200 flex items-center">
                                <i class="fas fa-user-graduate mr-3 text-indigo-500"></i>
                                Student Login
                            </a>
                            <a href="admin-login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200 flex items-center">
                                <i class="fas fa-user-shield mr-3 text-indigo-500"></i>
                                Admin Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="hamburger p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                        <div class="w-6 h-6 flex flex-col justify-between">
                            <span class="hamburger-line w-full h-0.5 bg-gray-700 rounded"></span>
                            <span class="hamburger-line w-full h-0.5 bg-gray-700 rounded"></span>
                            <span class="hamburger-line w-full h-0.5 bg-gray-700 rounded"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>

    <!-- Mobile Menu Sidebar -->
    <div id="mobile-menu-container" class="mobile-menu-container">
        <div class="p-6">
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chair text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($settings['site_title']); ?></h2>
                    </div>
                </div>
                <button id="mobile-menu-close" class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-times text-gray-600 text-lg"></i>
                </button>
            </div>

            <!-- Mobile Menu Links -->
            <div class="space-y-2 mb-8">
                <a href="index.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-home mr-3 text-indigo-500 w-5"></i>
                    Home
                </a>
                <a href="about.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-info-circle mr-3 text-indigo-500 w-5"></i>
                    About
                </a>
                <a href="find_seat.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-search mr-3 text-indigo-500 w-5"></i>
                    Find Seat
                </a>
                <a href="notice.php" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-bullhorn mr-3 text-indigo-500 w-5"></i>
                    Notices
                </a>
                <a href="department.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-university mr-3 text-indigo-500 w-5"></i>
                    Departments
                </a>
                <a href="contact.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-envelope mr-3 text-indigo-500 w-5"></i>
                    Contact
                </a>
            </div>

            <!-- Mobile Login Section -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider px-4 mb-4">Account</h3>
                <div class="space-y-2">
                    <a href="login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                        <i class="fas fa-user-graduate mr-3 text-indigo-500 w-5"></i>
                        Student Login
                    </a>
                    <a href="admin-login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                        <i class="fas fa-user-shield mr-3 text-indigo-500 w-5"></i>
                        Admin Login
                    </a>
                </div>
            </div>

            <!-- Contact Info in Mobile Menu -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider px-4 mb-4">Contact Info</h3>
                <div class="space-y-3 px-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-phone mr-3 text-indigo-500 w-4"></i>
                        <span><?= htmlspecialchars($settings['contact_phone']); ?></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-envelope mr-3 text-indigo-500 w-4"></i>
                        <span><?= htmlspecialchars($settings['contact_email']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add padding for fixed navbar -->
    <div class="pt-16"></div>

    <!-- Hero Section -->
    <section class="relative gradient-bg py-20 md:py-28">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative max-w-6xl mx-auto px-6 text-center text-white">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up">
                <?= $current_notice ? htmlspecialchars($current_notice['title']) : 'Notices & Announcements' ?>
            </h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
                <?= $current_notice ? 'Notice Details' : 'Stay updated with the latest exam schedules, announcements, and institutional notices' ?>
            </p>
            
            <?php if (!$current_notice): ?>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#all-notices" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-list mr-2"></i> View All Notices
                </a>
                <a href="#recent-updates" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-clock mr-2"></i> Recent Updates
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Floating Elements -->
        <?php if (!$current_notice): ?>
        <div class="absolute bottom-10 left-10 floating" data-aos="fade-right" data-aos-delay="500">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-bullhorn text-white text-2xl"></i>
            </div>
        </div>
        <div class="absolute top-10 right-10 floating" data-aos="fade-left" data-aos-delay="700" style="animation-delay: 0.5s;">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-calendar-alt text-white text-2xl"></i>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- Single Notice View -->
    <?php if ($current_notice): ?>
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" data-aos="fade-up">
                <!-- Notice Header -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($current_notice['title']); ?></h2>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-general">General</span>
                            <?php 
                            // Determine if notice is new (within 7 days)
                            $posted_date = strtotime($current_notice['posted_at']);
                            $current_date = time();
                            $days_diff = ($current_date - $posted_date) / (60 * 60 * 24);
                            
                            if ($days_diff <= 7) {
                                echo '<span class="badge badge-new">New</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 text-gray-600">
                        <i class="far fa-clock mr-1"></i> 
                        <?= date("F j, Y, g:i A", strtotime($current_notice['posted_at'])); ?>
                    </div>
                </div>
                
                <!-- Notice Content -->
                <div class="prose max-w-none mb-8">
                    <p class="text-gray-700 text-lg leading-relaxed"><?= nl2br(htmlspecialchars($current_notice['description'])); ?></p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                    <a href="notice.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to All Notices
                    </a>
                    <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                        <i class="fas fa-print mr-2"></i> Print Notice
                    </button>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Stats Section -->
    <?php if (!$current_notice): ?>
    <section class="py-12 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 mb-2"><?= $total_notices ?></div>
                    <p class="text-gray-600 font-medium">Total Notices</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl md:text-4xl font-bold text-green-600 mb-2">
                        <?php
                        // Count new notices (within last 7 days)
                        $new_count = 0;
                        $notices->data_seek(0);
                        while ($notice = $notices->fetch_assoc()) {
                            $posted_date = strtotime($notice['posted_at']);
                            $current_date = time();
                            $days_diff = ($current_date - $posted_date) / (60 * 60 * 24);
                            
                            if ($days_diff <= 7) {
                                $new_count++;
                            }
                        }
                        echo $new_count;
                        ?>
                    </div>
                    <p class="text-gray-600 font-medium">New This Week</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">
                        <?php
                        // Count notices from this month
                        $month_count = 0;
                        $notices->data_seek(0);
                        while ($notice = $notices->fetch_assoc()) {
                            $posted_date = strtotime($notice['posted_at']);
                            if (date('Y-m', $posted_date) === date('Y-m')) {
                                $month_count++;
                            }
                        }
                        echo $month_count;
                        ?>
                    </div>
                    <p class="text-gray-600 font-medium">This Month</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-3xl md:text-4xl font-bold text-purple-600 mb-2">
                        <?php
                        // Get the date of the latest notice
                        $notices->data_seek(0);
                        $latest_notice = $notices->fetch_assoc();
                        echo date('M j', strtotime($latest_notice['posted_at']));
                        ?>
                    </div>
                    <p class="text-gray-600 font-medium">Last Updated</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- All Notices Section -->
    <?php if (!$current_notice): ?>
    <section id="all-notices" class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">All Notices</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Browse through all institutional notices and announcements
                </p>
            </div>
            
            <!-- Filter Options -->
            <div class="flex flex-wrap justify-center gap-4 mb-8" data-aos="fade-up" data-aos-delay="100">
                <button class="filter-btn bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium transition" data-filter="all">
                    All Notices
                </button>
                <button class="filter-btn bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg font-medium transition hover:bg-gray-50" data-filter="new">
                    New This Week
                </button>
                <button class="filter-btn bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg font-medium transition hover:bg-gray-50" data-filter="exam">
                    Exam Related
                </button>
                <button class="filter-btn bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg font-medium transition hover:bg-gray-50" data-filter="important">
                    Important
                </button>
            </div>
            
            <!-- Notices Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6" id="notices-container">
                <?php 
                $notices->data_seek(0);
                $notice_count = 0;
                while ($row = $notices->fetch_assoc()): 
                    $notice_count++;
                    
                    // Determine notice type and styling
                    $notice_class = "notice-card general";
                    $badge_class = "badge-general";
                    $badge_text = "General";
                    
                    // Simple logic to categorize notices based on content
                    $title_lower = strtolower($row['title']);
                    $desc_lower = strtolower($row['description']);
                    
                    if (strpos($title_lower, 'exam') !== false || strpos($desc_lower, 'exam') !== false) {
                        $notice_class = "notice-card exam";
                        $badge_class = "badge-exam";
                        $badge_text = "Exam";
                    } elseif (strpos($title_lower, 'important') !== false || strpos($title_lower, 'urgent') !== false) {
                        $notice_class = "notice-card important";
                        $badge_class = "badge-important";
                        $badge_text = "Important";
                    }
                    
                    // Check if notice is new (within 7 days)
                    $posted_date = strtotime($row['posted_at']);
                    $current_date = time();
                    $days_diff = ($current_date - $posted_date) / (60 * 60 * 24);
                    $is_new = $days_diff <= 7;
                    
                    $delay = ($notice_count % 3) * 100;
                ?>
                    <div class="notice-item <?= $notice_class ?> rounded-xl p-6 card-hover" 
                         data-aos="fade-up" 
                         data-aos-delay="<?= $delay ?>"
                         data-category="<?= $badge_text === 'Exam' ? 'exam' : ($badge_text === 'Important' ? 'important' : 'general') ?>"
                         data-new="<?= $is_new ? 'true' : 'false' ?>">
                        <div class="flex justify-between items-start mb-4">
                            <span class="<?= $badge_class ?> badge"><?= $badge_text ?></span>
                            <?php if ($is_new): ?>
                                <span class="badge badge-new">New</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($row['title']); ?></h3>
                        <p class="text-gray-600 mb-4 line-clamp-3"><?= htmlspecialchars($row['description']); ?></p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><i class="far fa-clock mr-1"></i> <?= date("M j, Y", $posted_date); ?></span>
                            <a href="notice.php?id=<?= $row['notice_id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                Read More <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <?php if ($total_notices === 0): ?>
                    <div class="col-span-full text-center py-12">
                        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">No Notices Available</h3>
                        <p class="text-gray-600 max-w-md mx-auto">There are currently no notices posted. Please check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recent Updates Timeline -->
    <?php if (!$current_notice): ?>
    <section id="recent-updates" class="py-12 md:py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Recent Updates Timeline</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Track the latest announcements in chronological order
                </p>
            </div>
            
            <div class="timeline" data-aos="fade-up" data-aos-delay="100">
                <?php 
                $notices->data_seek(0);
                $timeline_count = 0;
                while ($row = $notices->fetch_assoc()): 
                    $timeline_count++;
                    if ($timeline_count > 5) break; // Show only 5 most recent in timeline
                    
                    $delay = $timeline_count * 100;
                ?>
                    <div class="timeline-item" data-aos="fade-right" data-aos-delay="<?= $delay ?>">
                        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-600">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($row['title']); ?></h3>
                                <span class="text-sm text-gray-500"><?= date("M j, Y", strtotime($row['posted_at'])); ?></span>
                            </div>
                            <p class="text-gray-600 mb-3"><?= htmlspecialchars($row['description']); ?></p>
                            <a href="notice.php?id=<?= $row['notice_id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                Read full notice <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($total_notices > 5): ?>
                <div class="text-center mt-8" data-aos="fade-up">
                    <a href="#all-notices" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                        View All Notices <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Subscription Section -->
    <?php if (!$current_notice): ?>
    <section class="py-12 md:py-16 bg-gradient-to-r from-indigo-500 to-purple-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">Stay Updated</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Get notified when new notices are published
            </p>
            <div class="max-w-md mx-auto" data-aos="zoom-in" data-aos-delay="200">
                <form class="flex flex-col sm:flex-row gap-4">
                    <input type="email" placeholder="Enter your email" class="flex-grow px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 text-gray-800">
                    <button type="submit" class="bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition flex items-center justify-center">
                        <i class="fas fa-bell mr-2"></i> Subscribe
                    </button>
                </form>
                <p class="text-indigo-200 text-sm mt-4">
                    We'll send you email notifications for important announcements
                </p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white pt-12 pb-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-chair text-indigo-400 text-2xl mr-2"></i>
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($settings['site_title']); ?></h3>
                    </div>
                    <p class="text-gray-400 mb-4">Efficient exam seat planning and management system for educational institutions.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition">About</a></li>
                        <li><a href="find_seat.php" class="text-gray-400 hover:text-white transition">Find Seat</a></li>
                        <li><a href="notice.php" class="text-gray-400 hover:text-white transition">Notices</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Exam Schedule</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Room Layouts</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Guidelines</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">FAQs</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mr-3 mt-1 text-indigo-400"></i>
                            <span>Educational Institution Campus, City, Country</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-indigo-400"></i>
                            <span><?= htmlspecialchars($settings['contact_phone']); ?></span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-indigo-400"></i>
                            <span><?= htmlspecialchars($settings['contact_email']); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-8 mt-8 border-t border-gray-700 text-center text-gray-400">
                <p><?= htmlspecialchars($settings['footer_text']); ?></p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const items = document.querySelectorAll("[data-aos-desktop]");

            items.forEach(el => {
                const desktopAnim = el.getAttribute("data-aos-desktop");

                if (window.innerWidth >= 768) {
                    el.setAttribute("data-aos", desktopAnim);
                } else {
                    el.setAttribute("data-aos", "fade-up");
                }
            });
        });
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuContainer = document.getElementById('mobile-menu-container');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const hamburger = document.querySelector('.hamburger');

        function openMobileMenu() {
            mobileMenuContainer.classList.add('open');
            mobileMenuOverlay.classList.add('open');
            hamburger.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileMenuContainer.classList.remove('open');
            mobileMenuOverlay.classList.remove('open');
            hamburger.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        mobileMenuClose.addEventListener('click', closeMobileMenu);
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Notice filtering functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('bg-indigo-600', 'text-white');
                    btn.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                });
                
                this.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                this.classList.add('bg-indigo-600', 'text-white');
                
                const filter = this.getAttribute('data-filter');
                const noticeItems = document.querySelectorAll('.notice-item');
                
                noticeItems.forEach(item => {
                    if (filter === 'all') {
                        item.style.display = 'block';
                    } else if (filter === 'new') {
                        if (item.getAttribute('data-new') === 'true') {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        if (item.getAttribute('data-category') === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>