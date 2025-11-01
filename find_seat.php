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

// Fetch departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");

// Fetch recent notices
$recent_notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC LIMIT 3");

$seat_info = null;
$show_modal = false;

if (isset($_POST['find_seat'])) {
    $department = $conn->real_escape_string($_POST['department']);
    $session    = $conn->real_escape_string($_POST['session']);
    $semester   = $conn->real_escape_string($_POST['semester']);
    $exam_type  = $conn->real_escape_string($_POST['exam_type']);
    $roll       = $conn->real_escape_string($_POST['roll']);

    // Query student info with more details using session field
    $query = "
        SELECT s.name, s.roll_no, s.registration_no, s.department, s.semester, s.session, 
               r.room_name, r.building_name, r.floor_number, sa.seat_number
        FROM students s
        LEFT JOIN seat_allocations sa ON s.student_id = sa.student_id
        LEFT JOIN rooms r ON sa.room_id = r.room_id
        WHERE s.roll_no = '$roll' AND s.department = '$department' 
        AND s.semester = '$semester' AND s.session = '$session'
        LIMIT 1
    ";

    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $seat_info = $result->fetch_assoc();
    } else {
        $seat_info = false; // no data found
    }
    
    // Set session flag to show modal only after form submission
    $_SESSION['show_seat_modal'] = true;
    $_SESSION['seat_info'] = $seat_info;
    
    // Redirect to clear POST data and prevent resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if we should show modal from session
if (isset($_SESSION['show_seat_modal'])) {
    $show_modal = true;
    $seat_info = $_SESSION['seat_info'];
    // Clear the session so modal doesn't show again on refresh
    unset($_SESSION['show_seat_modal']);
    unset($_SESSION['seat_info']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Seat - <?= htmlspecialchars($settings['site_title']); ?></title>
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
        
        /* Enhanced Seat Visualization */
        .classroom-container {
            background: #f9fafb;
            border-radius: 1rem;
            padding: 1.5rem;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .seat {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        @media (min-width: 768px) {
            .seat {
                width: 3rem;
                height: 3rem;
                font-size: 0.875rem;
            }
        }
        
        .seat.available {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }
        
        .seat.occupied {
            background: #fecaca;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .seat.current {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
            z-index: 10;
        }
        
        .seat:hover {
            transform: scale(1.05);
        }
        
        .teacher-desk {
            background: #e5e7eb;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 2px dashed #9ca3af;
        }
        
        @media (min-width: 768px) {
            .teacher-desk {
                height: 3rem;
            }
        }
        
        .seats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin: 0 auto;
            max-width: 20rem;
        }
        
        @media (min-width: 768px) {
            .seats-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 1rem;
                max-width: 24rem;
            }
        }
        
        /* Stats Counter */
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-section {
                display: block !important;
            }
        }
        
        .print-section {
            display: none;
        }
        
        /* Feature Icons */
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        /* Step by Step Process */
        .process-step {
            position: relative;
            padding-left: 80px;
            margin-bottom: 40px;
        }
        
        .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
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
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Professional Navbar -->
    <nav class="navbar fixed top-0 left-0 w-full z-50 no-print">
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
                    <a href="find_seat.php" class="nav-link text-indigo-600 active transition-colors duration-300">Find Seat</a>
                    <a href="notice.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Notices</a>
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
    <div id="mobile-menu-overlay" class="mobile-menu-overlay no-print"></div>

    <!-- Mobile Menu Sidebar -->
    <div id="mobile-menu-container" class="mobile-menu-container no-print">
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
                <a href="find_seat.php" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-search mr-3 text-indigo-500 w-5"></i>
                    Find Seat
                </a>
                <a href="notice.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
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
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up">Find Your Exam Seat</h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
                Quickly locate your assigned exam seat with our easy-to-use search system
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#search-form" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Find Your Seat Now
                </a>
                <a href="#how-it-works" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-play-circle mr-2"></i> How It Works
                </a>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute bottom-10 left-10 floating" data-aos="fade-up" data-aos-desktop="fade-right" data-aos-delay="500">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-chair text-white text-2xl"></i>
            </div>
        </div>
        <div class="absolute top-10 right-10 floating" data-aos="fade-up" data-aos-desktop="fade-left" data-aos-delay="700" style="animation-delay: 0.5s;">
            <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <i class="fas fa-university text-white text-2xl"></i>
            </div>
        </div>
    </section>

    
    <!-- Search Form Section -->
    <section id="search-form" class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Find Your Seat</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                    Enter your details below to locate your assigned exam seat and room information
                </p>
            </div>
            
            <div class="bg-white shadow-2xl rounded-2xl p-6 md:p-8 card-hover" data-aos="fade-up" data-aos-delay="100">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-building mr-2 text-indigo-500"></i>Department</label>
                        <select name="department" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Department</option>
                            <?php 
                            $departments->data_seek(0); // Reset pointer
                            while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($dept['dept_name']); ?>" <?= isset($_POST['department']) && $_POST['department'] == $dept['dept_name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>Session</label>
                        <select name="session" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Session</option>
                            <option value="2021-22" <?= isset($_POST['session']) && $_POST['session'] == '2021-22' ? 'selected' : '' ?>>2021-22</option>
                            <option value="2022-23" <?= isset($_POST['session']) && $_POST['session'] == '2022-23' ? 'selected' : '' ?>>2022-23</option>
                            <option value="2023-24" <?= isset($_POST['session']) && $_POST['session'] == '2023-24' ? 'selected' : '' ?>>2023-24</option>
                            <option value="2024-25" <?= isset($_POST['session']) && $_POST['session'] == '2024-25' ? 'selected' : '' ?>>2024-25</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-graduation-cap mr-2 text-indigo-500"></i>Semester</label>
                        <select name="semester" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Semester</option>
                            <option value="1st" <?= isset($_POST['semester']) && $_POST['semester'] == '1st' ? 'selected' : '' ?>>1st</option>
                            <option value="2nd" <?= isset($_POST['semester']) && $_POST['semester'] == '2nd' ? 'selected' : '' ?>>2nd</option>
                            <option value="3rd" <?= isset($_POST['semester']) && $_POST['semester'] == '3rd' ? 'selected' : '' ?>>3rd</option>
                            <option value="4th" <?= isset($_POST['semester']) && $_POST['semester'] == '4th' ? 'selected' : '' ?>>4th</option>
                            <option value="5th" <?= isset($_POST['semester']) && $_POST['semester'] == '5th' ? 'selected' : '' ?>>5th</option>
                            <option value="6th" <?= isset($_POST['semester']) && $_POST['semester'] == '6th' ? 'selected' : '' ?>>6th</option>
                            <option value="7th" <?= isset($_POST['semester']) && $_POST['semester'] == '7th' ? 'selected' : '' ?>>7th</option>
                            <option value="8th" <?= isset($_POST['semester']) && $_POST['semester'] == '8th' ? 'selected' : '' ?>>8th</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-file-alt mr-2 text-indigo-500"></i>Exam Type</label>
                        <select name="exam_type" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Exam Type</option>
                            <option value="Mid-Term" <?= isset($_POST['exam_type']) && $_POST['exam_type'] == 'Mid-Term' ? 'selected' : '' ?>>Mid-Term</option>
                            <option value="Final" <?= isset($_POST['exam_type']) && $_POST['exam_type'] == 'Final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-id-card mr-2 text-indigo-500"></i>Roll Number</label>
                        <input type="text" name="roll" placeholder="Enter Your Roll Number (e.g., 743738)" required 
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               value="<?= isset($_POST['roll']) ? htmlspecialchars($_POST['roll']) : '' ?>">
                    </div>
                    <div class="md:col-span-2 text-center pt-4">
                        <button type="submit" name="find_seat" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3 rounded-lg font-semibold transition flex items-center justify-center mx-auto shadow-lg">
                            <i class="fas fa-search mr-2"></i> Find My Seat
                        </button>
                    </div>
                </form>
                
                <!-- Help Text -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Need Help?
                    </h3>
                    <p class="text-blue-700 text-sm">
                        If you're having trouble finding your seat, please ensure all information is entered correctly. 
                        Contact your department administration if you continue to experience issues.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section (Visible when seat is found) -->
    <?php if ($show_modal && $seat_info): ?>
    <section class="py-16 bg-green-50" id="results-section">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8" data-aos="fade-up">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-green-600 text-3xl"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Seat Found Successfully!</h2>
                    <p class="text-gray-600">Here are your exam seat details</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <!-- Student Information -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Student Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Name:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['name']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Roll Number:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['roll_no']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Registration No:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['registration_no']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Department:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['department']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Semester:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['semester']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Session:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['session']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Exam Information -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Exam Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Room:</span>
                                <span class="text-indigo-600 font-semibold"><?= htmlspecialchars($seat_info['room_name'] ?? 'Not assigned'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Seat Number:</span>
                                <span class="text-indigo-600 font-semibold"><?= htmlspecialchars($seat_info['seat_number'] ?? 'Not assigned'); ?></span>
                            </div>
                            <?php if (isset($seat_info['building_name'])): ?>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Building:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['building_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($seat_info['floor_number'])): ?>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Floor:</span>
                                <span class="text-gray-900"><?= htmlspecialchars($seat_info['floor_number']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Classroom Visualization -->
                <div class="mt-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Classroom Layout - <?= htmlspecialchars($seat_info['room_name'] ?? 'Room'); ?></h3>
                    
                    <div class="classroom-container">
                        <div class="text-center mb-4 md:mb-6">
                            <div class="w-12 h-6 md:w-16 md:h-8 bg-gray-300 rounded-lg mx-auto mb-2"></div>
                            <p class="text-xs md:text-sm text-gray-600">Teacher's Desk</p>
                        </div>
                        
                        <div class="seats-grid">
                            <?php
                            $userSeat = $seat_info['seat_number'] ?? null;
                            $occupiedSeats = ['A-7', 'A-13', 'A-14', 'A-21', 'A-26', 'A-28', 'A-35', 'A-39', 'A-42', 'A-49'];
                            
                            for ($i = 1; $i <= 50; $i++) {
                                $seatNumber = "A-" . $i;
                                $seatClass = "seat";
                                
                                // Determine seat status
                                if ($userSeat && $userSeat == $seatNumber) {
                                    $seatClass .= " current";
                                } elseif (in_array($seatNumber, $occupiedSeats)) {
                                    $seatClass .= " occupied";
                                } else {
                                    $seatClass .= " available";
                                }
                                
                                echo "<div class='$seatClass'>$seatNumber</div>";
                            }
                            ?>
                        </div>
                        
                        <div class="flex justify-center mt-6 md:mt-8 space-x-4 md:space-x-6">
                            <div class="flex items-center">
                                <div class="w-3 h-3 md:w-4 md:h-4 bg-green-100 border border-green-300 rounded mr-1 md:mr-2"></div>
                                <span class="text-xs md:text-sm text-gray-600">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 md:w-4 md:h-4 bg-red-100 border border-red-300 rounded mr-1 md:mr-2"></div>
                                <span class="text-xs md:text-sm text-gray-600">Occupied</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 md:w-4 md:h-4 bg-blue-100 border border-blue-500 rounded mr-1 md:mr-2"></div>
                                <span class="text-xs md:text-sm text-gray-600">Your Seat</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                    <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                        <i class="fas fa-print mr-2"></i> Print Details
                    </button>
                    <a href="find_seat.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Search Again
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">How It Works</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Follow these simple steps to find your exam seat quickly and easily
                </p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <div class="process-step" data-aos="fade-up" data-aos-desktop="fade-right">
                    <div class="step-number">1</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Enter Your Details</h3>
                    <p class="text-gray-600">
                        Fill in the search form with your department, session, semester, exam type, and roll number.
                    </p>
                </div>
                
                <div class="process-step" data-aos="fade-up" data-aos-desktop="fade-right" data-aos-delay="100">
                    <div class="step-number">2</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Submit The Form</h3>
                    <p class="text-gray-600">
                        Click the "Find My Seat" button to search for your assigned seat in our database.
                    </p>
                </div>
                
                <div class="process-step" data-aos="fade-up" data-aos-desktop="fade-right" data-aos-delay="200">
                    <div class="step-number">3</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">View Your Seat Details</h3>
                    <p class="text-gray-600">
                        Instantly see your assigned room, seat number, and a visual classroom layout.
                    </p>
                </div>
                
                <div class="process-step" data-aos="fade-up" data-aos-desktop="fade-right" data-aos-delay="300">
                    <div class="step-number">4</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Print or Save</h3>
                    <p class="text-gray-600">
                        Print your seat details or take a screenshot for easy reference on exam day.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Why Use Our System</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Discover the benefits of using our seat plan management system
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-md text-center card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon bg-blue-100">
                        <i class="fas fa-bolt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Quick & Easy</h3>
                    <p class="text-gray-600">
                        Find your seat in seconds with our simple and intuitive search interface.
                    </p>
                </div>
                
                <div class="bg-white p-8 rounded-2xl shadow-md text-center card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon bg-green-100">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Accurate Information</h3>
                    <p class="text-gray-600">
                        Get precise room and seat details directly from the official database.
                    </p>
                </div>
                
                <div class="bg-white p-8 rounded-2xl shadow-md text-center card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon bg-purple-100">
                        <i class="fas fa-mobile-alt text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Mobile Friendly</h3>
                    <p class="text-gray-600">
                        Access your seat information anytime, anywhere on any device.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Notices Section -->
    <section class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Recent Notices</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Stay updated with the latest exam-related announcements
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <?php 
                $recent_notices->data_seek(0); // Reset pointer
                $notice_count = 0;
                while ($row = $recent_notices->fetch_assoc()): 
                    $notice_count++;
                    $delay = ($notice_count % 3) * 100;
                ?>
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-600 card-hover" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-indigo-700"><?= htmlspecialchars($row['title']); ?></h3>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded">New</span>
                        </div>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($row['description']); ?></p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><i class="far fa-clock mr-1"></i> <?= date("F j, Y", strtotime($row['posted_at'])); ?></span>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Read More <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-10" data-aos="fade-up">
                <a href="index.php#notices" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                    View All Notices <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                    Find answers to common questions about the seat plan system
                </p>
            </div>
            
            <div class="space-y-6" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">What information do I need to find my seat?</h3>
                    <p class="text-gray-600">
                        You'll need your department, session, semester, exam type, and roll number to search for your seat.
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">What should I do if I can't find my seat?</h3>
                    <p class="text-gray-600">
                        Double-check that all information is entered correctly. If the problem persists, contact your department administration.
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Can I print my seat information?</h3>
                    <p class="text-gray-600">
                        Yes, you can print your seat details using the print button that appears after finding your seat.
                    </p>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">How often is the seat information updated?</h3>
                    <p class="text-gray-600">
                        Seat information is updated in real-time as changes are made by administrators.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 md:py-20 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">Ready to Find Your Seat?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Don't wait until the last minute. Find your exam seat now and be prepared!
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="200">
                <a href="#search-form" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Find My Seat Now
                </a>
                <a href="index.php#contact" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-question-circle mr-2"></i> Get Help
                </a>
            </div>
        </div>
    </section>

    <!-- Print Section (Hidden until printing) -->
    <div class="print-section">
        <div class="p-8">
            <div class="text-center mb-6 border-b pb-4">
                <h1 class="text-2xl font-bold"><?= htmlspecialchars($settings['site_title']); ?></h1>
                <h2 class="text-xl">Exam Seat Allocation</h2>
                <p class="text-gray-600">Generated on: <?= date('F j, Y g:i A'); ?></p>
            </div>
            
            <?php if ($show_modal && $seat_info): ?>
            <div class="mb-6">
                <h3 class="text-lg font-bold mb-2">Student Information</h3>
                <table class="w-full border-collapse border border-gray-300">
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Name</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['name']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Roll Number</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['roll_no']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Registration No</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['registration_no']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Department</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['department']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Semester</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['semester']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Session</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['session']); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Room</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['room_name'] ?? 'Not assigned'); ?></td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 font-medium">Seat Number</td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($seat_info['seat_number'] ?? 'Not assigned'); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="text-center text-sm text-gray-600 mt-8">
                <p>Please bring this printout and your student ID to the exam hall.</p>
                <p>Arrive at least 15 minutes before the exam starts.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white pt-12 pb-6 no-print">
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
                        <li><a href="index.php#notices" class="text-gray-400 hover:text-white transition">Notices</a></li>
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

        // Scroll to results section if seat is found
        <?php if ($show_modal && $seat_info): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('results-section').scrollIntoView({
                    behavior: 'smooth'
                });
            }, 500);
        });
        <?php endif; ?>

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const rollInput = document.querySelector('input[name="roll"]');
            const rollValue = rollInput.value.trim();
            
            // Basic validation for roll number format
            if (rollValue && !/^[0-9]+$/.test(rollValue)) {
                e.preventDefault();
                alert('Please enter a valid roll number (numbers only).');
                rollInput.focus();
            }
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