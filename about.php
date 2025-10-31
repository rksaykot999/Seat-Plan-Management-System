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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?= htmlspecialchars($settings['site_title']); ?></title>
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
        
        /* Professional Navbar Styles - FIXED */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
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
        
        /* Mobile Menu Styles - FIXED */
        .mobile-menu-container {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 320px;
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
        
        /* Login Dropdown - FIXED */
        .login-group {
            position: relative;
        }
        
        .login-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 100;
        }
        
        .login-group:hover .login-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Hamburger Animation - FIXED */
        .hamburger {
            transition: all 0.3s ease;
        }
        
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
        
        /* Timeline Styles */
        .timeline-item {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 12px;
            width: 2px;
            height: calc(100% + 1rem);
            background: #e2e8f0;
        }
        
        .timeline-item:last-child::after {
            display: none;
        }
        
        /* Stats Counter */
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Prevent horizontal scroll - NEW */
        body {
            overflow-x: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Professional Navbar - FIXED -->
    <nav class="navbar fixed top-0 left-0 w-full">
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
                    <a href="about.php" class="nav-link text-indigo-600 active transition-colors duration-300">About</a>
                    <a href="find_seat.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Find Seat</a>
                    <a href="notice.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Notices</a>
                    <a href="department.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Departments</a>
                    <a href="contact.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Contact</a>

                    <!-- Login Dropdown - FIXED -->
                    <div class="login-group relative">
                        <button class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300 flex items-center space-x-1">
                            <span>Login</span>
                            <i class="fas fa-chevron-down text-xs mt-1"></i>
                        </button>
                        <div class="login-dropdown w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
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

                <!-- Mobile menu button - FIXED -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="hamburger p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 focus:outline-none">
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

    <!-- Mobile Menu Overlay - FIXED -->
    <div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>

    <!-- Mobile Menu Sidebar - FIXED -->
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
                <button id="mobile-menu-close" class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 focus:outline-none">
                    <i class="fas fa-times text-gray-600 text-lg"></i>
                </button>
            </div>

            <!-- Mobile Menu Links -->
            <div class="space-y-2 mb-8">
                <a href="index.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-home mr-3 text-indigo-500 w-5"></i>
                    Home
                </a>
                <a href="about.php" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
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
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up">About Us</h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
                Learn more about our mission, values, and the team behind the Seat Plan Management System
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#mission" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-bullseye mr-2"></i> Our Mission
                </a>
                <a href="#team" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-users mr-2"></i> Meet Our Team
                </a>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section id="mission" class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Mission & Vision</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    We are dedicated to revolutionizing the way educational institutions manage exam seating arrangements.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12">
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 card-hover" data-aos="fade-up" data-aos-desktop="fade-right">
                    <div class="w-16 h-16 bg-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    <p class="text-gray-600 mb-4">
                        To provide educational institutions with an efficient, transparent, and user-friendly platform for managing exam seating arrangements, reducing administrative burden while enhancing the student experience.
                    </p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-indigo-500 mr-3 mt-1"></i>
                            <span>Simplify seat allocation processes</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-indigo-500 mr-3 mt-1"></i>
                            <span>Ensure fair and transparent seat distribution</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-indigo-500 mr-3 mt-1"></i>
                            <span>Reduce administrative workload</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 card-hover" data-aos="fade-up" data-aos-desktop="fade-left">
                    <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600 mb-4">
                        To become the leading seat plan management solution globally, setting new standards for efficiency, accessibility, and innovation in educational administration.
                    </p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-rocket text-purple-500 mr-3 mt-1"></i>
                            <span>Innovate continuously to meet evolving needs</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-rocket text-purple-500 mr-3 mt-1"></i>
                            <span>Expand to serve institutions worldwide</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-rocket text-purple-500 mr-3 mt-1"></i>
                            <span>Set benchmarks in educational technology</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-number mb-2">500+</div>
                    <p class="text-gray-600 font-medium">Students Served</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-number mb-2">50+</div>
                    <p class="text-gray-600 font-medium">Exam Rooms</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-number mb-2">10+</div>
                    <p class="text-gray-600 font-medium">Departments</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-number mb-2">99%</div>
                    <p class="text-gray-600 font-medium">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-up" data-aos-desktop="fade-right">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <p class="text-gray-600 mb-6">
                        The Seat Plan Management System was born out of a recognized need to modernize the traditional, paper-based exam seating arrangement process. We observed the challenges faced by educational institutions in managing large-scale examinations efficiently.
                    </p>
                    <p class="text-gray-600 mb-6">
                        Our journey began in 2020 when a group of educators and technology enthusiasts came together with a shared vision: to transform how institutions handle exam logistics. We combined our expertise in education and software development to create a solution that addresses real-world challenges.
                    </p>
                    <p class="text-gray-600">
                        Today, we continue to evolve our platform based on feedback from administrators, faculty, and students, ensuring we remain at the forefront of educational technology innovation.
                    </p>
                </div>
                <div class="relative" data-aos="fade-up" data-aos-desktop="fade-left">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">Our Timeline</h3>
                        <div class="space-y-6">
                            <div class="timeline-item">
                                <h4 class="font-bold text-lg">Concept Development</h4>
                                <p class="text-indigo-100">2020 - Identified the need for digital seat management</p>
                            </div>
                            <div class="timeline-item">
                                <h4 class="font-bold text-lg">Prototype Launch</h4>
                                <p class="text-indigo-100">2021 - Developed and tested initial version</p>
                            </div>
                            <div class="timeline-item">
                                <h4 class="font-bold text-lg">Pilot Implementation</h4>
                                <p class="text-indigo-100">2022 - Deployed in 3 educational institutions</p>
                            </div>
                            <div class="timeline-item">
                                <h4 class="font-bold text-lg">Full Launch</h4>
                                <p class="text-indigo-100">2023 - Expanded to serve multiple institutions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Meet Our Team</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Our diverse team of professionals brings together expertise in education, technology, and administration.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white rounded-2xl p-6 text-center card-hover" data-aos="zoom-in" data-aos-delay="100">
                    <div class="w-24 h-24 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold">
                        JD
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">John Doe</h3>
                    <p class="text-indigo-600 font-medium mb-3">Project Lead</p>
                    <p class="text-gray-600 text-sm">
                        With over 10 years in educational technology, John drives our vision and strategic direction.
                    </p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 text-center card-hover" data-aos="zoom-in" data-aos-delay="200">
                    <div class="w-24 h-24 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold">
                        SJ
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Sarah Johnson</h3>
                    <p class="text-indigo-600 font-medium mb-3">Technical Director</p>
                    <p class="text-gray-600 text-sm">
                        Sarah leads our development team with expertise in scalable software architecture and database design.
                    </p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 text-center card-hover" data-aos="zoom-in" data-aos-delay="300">
                    <div class="w-24 h-24 bg-gradient-to-r from-green-400 to-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold">
                        MR
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Michael Roberts</h3>
                    <p class="text-indigo-600 font-medium mb-3">Education Consultant</p>
                    <p class="text-gray-600 text-sm">
                        Michael brings 15 years of administrative experience in higher education institutions.
                    </p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-6 text-center card-hover" data-aos="zoom-in" data-aos-delay="400">
                    <div class="w-24 h-24 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold">
                        ED
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Emily Davis</h3>
                    <p class="text-indigo-600 font-medium mb-3">UX/UI Designer</p>
                    <p class="text-gray-600 text-sm">
                        Emily creates intuitive user experiences that make our platform accessible to all users.
                    </p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition"><i class="fab fa-dribbble"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Values</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    These core principles guide everything we do and shape our approach to serving educational institutions.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-20 h-20 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Reliability</h3>
                    <p class="text-gray-600">
                        We prioritize system stability and data integrity, ensuring our platform is always available when institutions need it most.
                    </p>
                </div>
                
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-lightbulb text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Innovation</h3>
                    <p class="text-gray-600">
                        We continuously explore new technologies and approaches to enhance our platform and better serve our users.
                    </p>
                </div>
                
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-20 h-20 bg-pink-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-handshake text-pink-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Partnership</h3>
                    <p class="text-gray-600">
                        We view our relationship with educational institutions as partnerships, working together to achieve shared goals.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 md:py-20 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">Ready to Transform Your Exam Management?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Join the growing number of institutions using our system to streamline their exam processes.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="200">
                <a href="index.php#contact" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-envelope mr-2"></i> Contact Us
                </a>
                <a href="index.php#find" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Try Seat Finder
                </a>
            </div>
        </div>
    </section>

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
                        <li><a href="index.php#find" class="text-gray-400 hover:text-white transition">Find Seat</a></li>
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
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Mobile menu functionality - FIXED
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

        // Event listeners - FIXED
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', openMobileMenu);
        }
        
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }
        
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        // Close mobile menu when clicking on a link - FIXED
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Close mobile menu when pressing Escape key - NEW
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMobileMenu();
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