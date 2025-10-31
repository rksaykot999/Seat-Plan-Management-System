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

// Process contact form submission
$form_success = false;
$form_error = false;
$error_message = "";

if (isset($_POST['send_message'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $form_error = true;
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = true;
        $error_message = "Please enter a valid email address.";
    } else {
        // Insert into feedback table
        $insert_query = "INSERT INTO feedback (student_name, email, message) VALUES ('$name', '$email', '$message')";
        if ($conn->query($insert_query)) {
            $form_success = true;
        } else {
            $form_error = true;
            $error_message = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}

// Fetch FAQ data (we'll create a sample array for now)
$faqs = [
    [
        'question' => 'How can I find my exam seat?',
        'answer' => 'You can find your exam seat by using the "Find Your Seat" feature on our website. Enter your department, session, semester, exam type, and roll number to get your seat details.'
    ],
    [
        'question' => 'What should I do if I can\'t find my seat information?',
        'answer' => 'If you cannot find your seat information, please double-check that you\'ve entered all details correctly. If the problem persists, contact your department administration or visit the exam controller\'s office.'
    ],
    [
        'question' => 'How early should I arrive for my exam?',
        'answer' => 'We recommend arriving at least 15 minutes before your exam starts. This gives you enough time to find your room and seat without rushing.'
    ],
    [
        'question' => 'Can I change my exam seat?',
        'answer' => 'Exam seats are assigned systematically and cannot be changed without proper authorization from the exam controller\'s office. Special circumstances may be considered with proper documentation.'
    ],
    [
        'question' => 'What documents do I need to bring to the exam?',
        'answer' => 'You must bring your student ID card and admit card (if applicable). Some exams may require additional materials, which will be specified in the exam notice.'
    ],
    [
        'question' => 'How can I get in touch with my department?',
        'answer' => 'You can find department contact information on the Departments page. Each department has specific office hours and contact details listed.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?= htmlspecialchars($settings['site_title']); ?></title>
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
        
        /* Contact Card Styles */
        .contact-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .contact-card.primary {
            border-left-color: #667eea;
            background: linear-gradient(to right, #ebf8ff, #ffffff);
        }
        
        .contact-card.success {
            border-left-color: #48bb78;
            background: linear-gradient(to right, #f0fff4, #ffffff);
        }
        
        .contact-card.warning {
            border-left-color: #ed8936;
            background: linear-gradient(to right, #fffaf0, #ffffff);
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
        
        /* FAQ Styles */
        .faq-item {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .faq-question {
            padding: 1.5rem 0;
            cursor: pointer;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .faq-answer.open {
            max-height: 500px;
        }
        
        .faq-icon {
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }
        
        /* Form Styles */
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Map Container */
        .map-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
                    <a href="department.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Departments</a>
                    <a href="contact.php" class="nav-link text-indigo-600 active transition-colors duration-300">Contact</a>
                    
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
                <a href="department.php" class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-colors duration-200 flex items-center">
                    <i class="fas fa-university mr-3 text-indigo-500 w-5"></i>
                    Departments
                </a>
                <a href="contact.php" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
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
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up">Contact Us</h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
                Get in touch with us for any questions or assistance regarding exam seat plans
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#contact-form" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-envelope mr-2"></i> Send Message
                </a>
                <a href="#faq" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-question-circle mr-2"></i> View FAQ
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Get In Touch</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    We're here to help with any questions about exam seat plans, technical issues, or general inquiries
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="contact-card primary rounded-xl p-6 text-center card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone-alt text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Phone</h3>
                    <p class="text-gray-600 mb-4">Call us during office hours</p>
                    <a href="tel:<?= htmlspecialchars($settings['contact_phone']); ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        <?= htmlspecialchars($settings['contact_phone']); ?>
                    </a>
                </div>
                
                <div class="contact-card success rounded-xl p-6 text-center card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Email</h3>
                    <p class="text-gray-600 mb-4">Send us an email anytime</p>
                    <a href="mailto:<?= htmlspecialchars($settings['contact_email']); ?>" class="text-green-600 hover:text-green-800 font-medium">
                        <?= htmlspecialchars($settings['contact_email']); ?>
                    </a>
                </div>
                
                <div class="contact-card warning rounded-xl p-6 text-center card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-orange-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Location</h3>
                    <p class="text-gray-600 mb-4">Visit our campus</p>
                    <p class="text-orange-600 font-medium">Educational Institution Campus</p>
                </div>
                
                <div class="contact-card primary rounded-xl p-6 text-center card-hover" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Office Hours</h3>
                    <p class="text-gray-600 mb-2">Mon - Fri: 9:00 AM - 5:00 PM</p>
                    <p class="text-gray-600">Sat: 9:00 AM - 1:00 PM</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Map Section -->
    <section id="contact-form" class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-2 gap-12">

                <!-- Contact Form -->
                <div data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Send Us a Message</h2>
                    <p class="text-gray-600 mb-8">
                        Have questions about exam seat plans or facing technical issues? Fill out the form and we will respond shortly.
                    </p>

                    <form method="POST" class="space-y-6 w-full">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="name" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="Enter full name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="Enter email">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                            <input type="text" name="subject" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Enter subject">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea name="message" rows="6" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Enter message"></textarea>
                        </div>

                        <button type="submit" name="send_message" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i> Send Message
                        </button>
                    </form>
                </div>

                <!-- Map & Contact Info -->
                <div data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Visit Our Campus</h2>

                    <div class="mb-8 w-full h-72 md:h-80 bg-gray-200 rounded-xl flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600 font-medium">Interactive Campus Map</p>
                            <p class="text-gray-500 text-sm mt-2">Map will appear here</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Additional Information</h3>

                        <div class="space-y-4">

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-map-marker-alt text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700">Main Campus Address</h4>
                                    <p class="text-gray-600">Educational Institution Campus<br>123 University Road<br>City, State 12345<br>Country</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-clock text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700">Office Hours</h4>
                                    <p class="text-gray-600">Mon - Thu: 9:00 AM - 5:00 PM<br>Friday: 9:00 AM - 1:00 PM<br>Sat - Sun: Closed</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-exclamation-circle text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700">Emergency Contact</h4>
                                    <p class="text-gray-600">For urgent exam-related issues during exams.</p>
                                    <p class="text-indigo-600 font-medium mt-1">+880 1700 123456</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>


    <!-- FAQ Section -->
    <section id="faq" class="py-12 md:py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Find quick answers to common questions about exam seat plans and the system
                </p>
            </div>

            <div class="bg-white px-6 rounded-xl shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item <?= $index === 0 ? 'active' : '' ?>" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            <h3 class="text-lg font-semibold text-gray-800 flex-grow"><?= $faq['question'] ?></h3>
                            <i class="fas fa-chevron-down faq-icon text-indigo-600"></i>
                        </div>
                        <div class="faq-answer <?= $index === 0 ? 'open' : '' ?>">
                            <div class="pb-6 px-6 text-gray-600">
                                <?= $faq['answer'] ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8" data-aos="fade-up">
                <p class="text-gray-600 mb-4">Still have questions?</p>
                <a href="#contact-form" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center">
                    <i class="fas fa-envelope mr-2"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- Departments Quick Contact -->
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Department Contacts</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Need to contact a specific department? Here are the main department contacts
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Fetch departments for contact information
                $departments = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");
                $dept_count = 0;
                while ($dept = $departments->fetch_assoc()): 
                    $dept_count++;
                    $delay = ($dept_count % 3) * 100;
                ?>
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-university text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($dept['dept_name']); ?></h3>
                                <p class="text-gray-600 text-sm">Department</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-user-tie mr-3 text-indigo-500 w-4"></i>
                                <span><?= htmlspecialchars($dept['head_name']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope mr-3 text-indigo-500 w-4"></i>
                                <span><?= htmlspecialchars($dept['contact_email']); ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="department.php?id=<?= $dept['dept_id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                View Department Details
                                <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-16 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6" data-aos="fade-up">Need Immediate Assistance?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Don't hesitate to reach out if you have urgent exam-related questions
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="200">
                <a href="tel:<?= htmlspecialchars($settings['contact_phone']); ?>" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-phone-alt mr-2"></i> Call Now
                </a>
                <a href="#contact-form" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-envelope mr-2"></i> Send Message
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
                        <li><a href="find_seat.php" class="text-gray-400 hover:text-white transition">Find Seat</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="notice.php" class="text-gray-400 hover:text-white transition">Notices</a></li>
                        <li><a href="department.php" class="text-gray-400 hover:text-white transition">Departments</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Exam Schedule</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Guidelines</a></li>
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

        // FAQ toggle functionality
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                    item.querySelector('.faq-answer').classList.remove('open');
                }
            });
            
            // Toggle current FAQ
            faqItem.classList.toggle('active');
            answer.classList.toggle('open');
        }

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

        // Form validation
        const contactForm = document.querySelector('form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input[required], textarea[required]');
                let valid = true;
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>