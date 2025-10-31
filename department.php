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

// Fetch all departments
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");

// Count total departments
$total_departments = $departments->num_rows;

// Check if we're viewing a specific department
$current_department = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $dept_id = $conn->real_escape_string($_GET['id']);
    $dept_query = $conn->query("SELECT * FROM departments WHERE dept_id = '$dept_id'");
    if ($dept_query && $dept_query->num_rows > 0) {
        $current_department = $dept_query->fetch_assoc();
        
        // Fetch students count for this department
        $student_count_query = $conn->query("SELECT COUNT(*) as total FROM students WHERE department = '".$current_department['dept_name']."'");
        $student_count = $student_count_query->fetch_assoc()['total'];
        
        // Fetch recent notices for this department (if we had department-specific notices)
        $recent_notices = $conn->query("SELECT * FROM notices WHERE title LIKE '%".$current_department['dept_name']."%' ORDER BY posted_at DESC LIMIT 3");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_department ? htmlspecialchars($current_department['dept_name']) . ' - ' : '' ?>Departments - <?= htmlspecialchars($settings['site_title']); ?></title>
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
        
        /* Department Card Styles */
        .department-card {
            border-top: 4px solid;
            transition: all 0.3s ease;
        }
        
        .department-card.cs {
            border-top-color: #3182ce;
            background: linear-gradient(to bottom, #ebf8ff, #ffffff);
        }
        
        .department-card.ee {
            border-top-color: #38a169;
            background: linear-gradient(to bottom, #f0fff4, #ffffff);
        }
        
        .department-card.ce {
            border-top-color: #dd6b20;
            background: linear-gradient(to bottom, #fffaf0, #ffffff);
        }
        
        .department-card.other {
            border-top-color: #805ad5;
            background: linear-gradient(to bottom, #faf5ff, #ffffff);
        }
        
        /* Stats Styles */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
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
        
        /* Department Icon Styles */
        .dept-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .dept-icon.cs {
            background: linear-gradient(135deg, #3182ce 0%, #63b3ed 100%);
        }
        
        .dept-icon.ee {
            background: linear-gradient(135deg, #38a169 0%, #68d391 100%);
        }
        
        .dept-icon.ce {
            background: linear-gradient(135deg, #dd6b20 0%, #f6ad55 100%);
        }
        
        .dept-icon.other {
            background: linear-gradient(135deg, #805ad5 0%, #b794f4 100%);
        }
        
        /* Feature List */
        .feature-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        /* Tab Styles */
        .tab-button {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .tab-button.active {
            background: #667eea;
            color: white;
        }
        
        .tab-button:not(.active) {
            background: #f7fafc;
            color: #4a5568;
        }
        
        .tab-button:not(.active):hover {
            background: #edf2f7;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
                    <a href="notice.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Notices</a>
                    <a href="department.php" class="nav-link text-indigo-600 active transition-colors duration-300">Departments</a>
                    <a href="contact.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Contact</a>
                    
                    <!-- Login Dropdown -->
                    <div class="login-group relative">
                        <button class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300 flex items-center space-x-1">
                            <span>Login</span>
                            <i class="fas fa-chevron-down text-xs mt-1"></i>
                        </button>
                        <div class="login-dropdown absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
                            <a href="student/login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200 flex items-center">
                                <i class="fas fa-user-graduate mr-3 text-indigo-500"></i>
                                Student Login
                            </a>
                            <a href="admin/login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200 flex items-center">
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
                <a href="notice.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-bullhorn mr-3 text-indigo-500 w-5"></i>
                    Notices
                </a>
                <a href="department.php" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
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
                    <a href="student/login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                        <i class="fas fa-user-graduate mr-3 text-indigo-500 w-5"></i>
                        Student Login
                    </a>
                    <a href="admin/login.php" class="block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
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
                <?= $current_department ? htmlspecialchars($current_department['dept_name']) : 'Academic Departments' ?>
            </h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
                <?= $current_department ? 'Department Details & Information' : 'Explore our diverse academic departments and programs' ?>
            </p>
            
            <?php if (!$current_department): ?>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#all-departments" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-list mr-2"></i> View All Departments
                </a>
                <a href="#department-stats" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-chart-bar mr-2"></i> Department Stats
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Floating Elements -->
        <?php if (!$current_department): ?>
        <div class="absolute bottom-10 left-10 floating" data-aos="fade-up" data-aos-desktop="fade-right" data-aos-delay="500">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-university text-white text-2xl"></i>
            </div>
        </div>
        <div class="absolute top-10 right-10 floating" data-aos="fade-up" data-aos-desktop="fade-left" data-aos-delay="700" style="animation-delay: 0.5s;">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-graduation-cap text-white text-2xl"></i>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- Single Department View -->
    <?php if ($current_department): ?>
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <!-- Breadcrumb -->
            <div class="mb-8" data-aos="fade-up">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <a href="department.php" class="text-indigo-600 hover:text-indigo-800">Departments</a>
                        </li>
                        <li>
                            <span class="text-gray-400">/</span>
                        </li>
                        <li>
                            <span class="text-gray-600"><?= htmlspecialchars($current_department['dept_name']); ?></span>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden" data-aos="fade-up">
                <!-- Department Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold mb-2"><?= htmlspecialchars($current_department['dept_name']); ?></h2>
                            <p class="text-indigo-100 text-lg">Department of <?= htmlspecialchars($current_department['dept_name']); ?></p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold"><?= $student_count ?></div>
                                <div class="text-sm">Students</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Department Content -->
                <div class="p-8">
                    <div class="grid md:grid-cols-3 gap-8 mb-8">
                        <!-- Department Info -->
                        <div class="md:col-span-2">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Department Information</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-user-tie text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-700">Department Head</h4>
                                        <p class="text-gray-600"><?= htmlspecialchars($current_department['head_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-envelope text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-700">Contact Email</h4>
                                        <p class="text-gray-600"><?= htmlspecialchars($current_department['contact_email']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-clock text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-700">Office Hours</h4>
                                        <p class="text-gray-600">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Department Description -->
                            <div class="mt-8">
                                <h4 class="text-xl font-bold text-gray-800 mb-3">About the Department</h4>
                                <p class="text-gray-600 leading-relaxed">
                                    The <?= htmlspecialchars($current_department['dept_name']); ?> Department is committed to providing high-quality education and research opportunities. 
                                    Our faculty members are dedicated to nurturing the next generation of professionals and innovators in the field.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="find_seat.php" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center">
                                    <i class="fas fa-search mr-2"></i> Find Exam Seat
                                </a>
                                <a href="notice.php" class="w-full bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-lg font-medium transition flex items-center justify-center">
                                    <i class="fas fa-bullhorn mr-2"></i> View Notices
                                </a>
                                <button class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-3 rounded-lg font-medium transition flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i> Download Syllabus
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Department Tabs -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex space-x-4 mb-6 overflow-x-auto">
                            <button class="tab-button active" data-tab="programs">Academic Programs</button>
                            <button class="tab-button" data-tab="faculty">Faculty Members</button>
                            <button class="tab-button" data-tab="resources">Resources</button>
                            <button class="tab-button" data-tab="contact">Contact Info</button>
                        </div>
                        
                        <div class="tab-content active" id="programs">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Academic Programs</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-800 mb-2">Undergraduate Programs</h4>
                                    <ul class="feature-list text-gray-600">
                                        <li>Bachelor of Science in <?= htmlspecialchars($current_department['dept_name']); ?></li>
                                        <li>Bachelor of Technology</li>
                                        <li>Dual Degree Programs</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-800 mb-2">Postgraduate Programs</h4>
                                    <ul class="feature-list text-gray-600">
                                        <li>Master of Science</li>
                                        <li>Doctor of Philosophy (PhD)</li>
                                        <li>Postgraduate Diploma</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="faculty">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Faculty Members</h3>
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
                                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-user-tie text-indigo-600 text-xl"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($current_department['head_name']); ?></h4>
                                    <p class="text-indigo-600 text-sm mb-2">Department Head</p>
                                    <p class="text-gray-600 text-sm">Professor & Department Chair</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
                                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-user-graduate text-indigo-600 text-xl"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800">Dr. Sarah Johnson</h4>
                                    <p class="text-indigo-600 text-sm mb-2">Associate Professor</p>
                                    <p class="text-gray-600 text-sm">Research Coordinator</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
                                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-user text-indigo-600 text-xl"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800">Dr. Michael Chen</h4>
                                    <p class="text-indigo-600 text-sm mb-2">Assistant Professor</p>
                                    <p class="text-gray-600 text-sm">Academic Advisor</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="resources">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Department Resources</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-800 mb-3">Laboratories & Facilities</h4>
                                    <ul class="feature-list text-gray-600">
                                        <li>Advanced Computing Lab</li>
                                        <li>Research Laboratory</li>
                                        <li>Project Development Center</li>
                                        <li>Department Library</li>
                                    </ul>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <h4 class="text-lg font-bold text-gray-800 mb-3">Student Resources</h4>
                                    <ul class="feature-list text-gray-600">
                                        <li>Course Materials</li>
                                        <li>Research Papers</li>
                                        <li>Academic Calendar</li>
                                        <li>Exam Schedule</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="contact">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Contact Information</h3>
                            <div class="bg-gray-50 rounded-xl p-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-3">Department Office</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-start">
                                                <i class="fas fa-map-marker-alt text-indigo-600 mt-1 mr-3"></i>
                                                <div>
                                                    <p class="font-medium text-gray-700">Address</p>
                                                    <p class="text-gray-600"><?= htmlspecialchars($current_department['dept_name']); ?> Department<br>Main Academic Building<br>Room 301</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-phone text-indigo-600 mr-3"></i>
                                                <div>
                                                    <p class="font-medium text-gray-700">Phone</p>
                                                    <p class="text-gray-600">+880 2 5566 7788</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-envelope text-indigo-600 mr-3"></i>
                                                <div>
                                                    <p class="font-medium text-gray-700">Email</p>
                                                    <p class="text-gray-600"><?= htmlspecialchars($current_department['contact_email']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-3">Office Hours</h4>
                                        <div class="space-y-2 text-gray-600">
                                            <div class="flex justify-between">
                                                <span>Monday - Thursday</span>
                                                <span>9:00 AM - 5:00 PM</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Friday</span>
                                                <span>9:00 AM - 1:00 PM</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Saturday - Sunday</span>
                                                <span>Closed</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-6">
                                            <h4 class="font-bold text-gray-800 mb-2">Quick Links</h4>
                                            <div class="space-y-2">
                                                <a href="#" class="block text-indigo-600 hover:text-indigo-800">Department Website</a>
                                                <a href="#" class="block text-indigo-600 hover:text-indigo-800">Faculty Directory</a>
                                                <a href="#" class="block text-indigo-600 hover:text-indigo-800">Research Publications</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Back Button -->
            <div class="mt-8 text-center" data-aos="fade-up">
                <a href="department.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center inline-flex">
                    <i class="fas fa-arrow-left mr-2"></i> Back to All Departments
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Stats Section -->
    <?php if (!$current_department): ?>
    <section id="department-stats" class="py-12 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Department Statistics</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Overview of our academic departments and their impact
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl md:text-4xl font-bold mb-2"><?= $total_departments ?></div>
                    <p class="text-indigo-100">Academic Departments</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl md:text-4xl font-bold mb-2">50+</div>
                    <p class="text-indigo-100">Faculty Members</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-3xl md:text-4xl font-bold mb-2">500+</div>
                    <p class="text-indigo-100">Students Enrolled</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-3xl md:text-4xl font-bold mb-2">15+</div>
                    <p class="text-indigo-100">Academic Programs</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- All Departments Section -->
    <?php if (!$current_department): ?>
    <section id="all-departments" class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Academic Departments</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Explore the diverse range of academic departments offering quality education and research opportunities
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $departments->data_seek(0);
                $dept_count = 0;
                while ($row = $departments->fetch_assoc()): 
                    $dept_count++;
                    
                    // Determine department class and icon
                    $dept_class = "department-card other";
                    $icon_class = "dept-icon other";
                    $icon_name = "fas fa-university";
                    
                    if (strpos(strtolower($row['dept_name']), 'computer') !== false) {
                        $dept_class = "department-card cs";
                        $icon_class = "dept-icon cs";
                        $icon_name = "fas fa-laptop-code";
                    } elseif (strpos(strtolower($row['dept_name']), 'electrical') !== false) {
                        $dept_class = "department-card ee";
                        $icon_class = "dept-icon ee";
                        $icon_name = "fas fa-bolt";
                    } elseif (strpos(strtolower($row['dept_name']), 'civil') !== false) {
                        $dept_class = "department-card ce";
                        $icon_class = "dept-icon ce";
                        $icon_name = "fas fa-hard-hat";
                    }
                    
                    $delay = ($dept_count % 3) * 100;
                ?>
                    <div class="<?= $dept_class ?> rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                        <div class="<?= $icon_class ?>">
                            <i class="<?= $icon_name ?> text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 text-center"><?= htmlspecialchars($row['dept_name']); ?></h3>
                        <div class="text-center mb-4">
                            <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Department
                            </span>
                        </div>
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-user-tie mr-3 text-indigo-500 w-4"></i>
                                <span>Head: <?= htmlspecialchars($row['head_name']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope mr-3 text-indigo-500 w-4"></i>
                                <span><?= htmlspecialchars($row['contact_email']); ?></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <a href="department.php?id=<?= $row['dept_id'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition inline-flex items-center">
                                View Details <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Programs Overview Section -->
    <?php if (!$current_department): ?>
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Academic Programs</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Comprehensive range of undergraduate and postgraduate programs
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div data-aos="fade-up" data-aos-desktop="fade-right">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Undergraduate Programs</h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Bachelor of Science (B.Sc.)</h4>
                            <p class="text-gray-600 mb-3">4-year comprehensive undergraduate program</p>
                            <ul class="feature-list text-gray-600 text-sm">
                                <li>Computer Science & Engineering</li>
                                <li>Electrical & Electronic Engineering</li>
                                <li>Civil Engineering</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Bachelor of Technology (B.Tech)</h4>
                            <p class="text-gray-600 mb-3">4-year technology-focused program</p>
                            <ul class="feature-list text-gray-600 text-sm">
                                <li>Information Technology</li>
                                <li>Electronics & Communication</li>
                                <li>Construction Technology</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div data-aos="fade-up" data-aos-desktop="fade-left">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Postgraduate Programs</h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Master of Science (M.Sc.)</h4>
                            <p class="text-gray-600 mb-3">2-year postgraduate program with research</p>
                            <ul class="feature-list text-gray-600 text-sm">
                                <li>Advanced Computer Science</li>
                                <li>Electrical Engineering</li>
                                <li>Structural Engineering</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Doctor of Philosophy (Ph.D.)</h4>
                            <p class="text-gray-600 mb-3">Research-based doctoral program</p>
                            <ul class="feature-list text-gray-600 text-sm">
                                <li>Computer Science & IT</li>
                                <li>Electrical & Electronics</li>
                                <li>Civil & Environmental</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <?php if (!$current_department): ?>
    <section class="py-12 md:py-16 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">Ready to Explore Further?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Discover more about our departments and find the right academic path for you
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="200">
                <a href="#all-departments" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-university mr-2"></i> Browse Departments
                </a>
                <a href="find_seat.php" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Find Your Seat
                </a>
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
                        <li><a href="department.php" class="text-gray-400 hover:text-white transition">Departments</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Academic Calendar</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Course Catalog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Faculty Directory</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Student Handbook</a></li>
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

        // Tab functionality for department details
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and contents
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show corresponding content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
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