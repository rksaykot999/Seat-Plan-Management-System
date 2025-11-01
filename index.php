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

// Start session to manage modal state
session_start();

// Fetch site settings
$settings = $conn->query("SELECT * FROM site_settings LIMIT 1")->fetch_assoc();

// Fetch notices
$notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC LIMIT 5");

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");

$seat_info = null;
$show_modal = false;

if (isset($_POST['find_seat'])) {
    $department = $conn->real_escape_string($_POST['department']);
    $session    = $conn->real_escape_string($_POST['session']);
    $semester   = $conn->real_escape_string($_POST['semester']);
    $exam_type  = $conn->real_escape_string($_POST['exam_type']);
    $roll       = $conn->real_escape_string($_POST['roll']);

    // Query student info
    $query = "
        SELECT s.name, s.roll_no, s.department, s.semester, r.room_name, sa.seat_number
        FROM students s
        LEFT JOIN seat_allocations sa ON s.student_id = sa.student_id
        LEFT JOIN rooms r ON sa.room_id = r.room_id
        WHERE s.roll_no = '$roll' AND s.department = '$department' AND s.semester = '$semester'
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
    <title><?= htmlspecialchars($settings['site_title']); ?></title>
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
                    <a href="#home" class="nav-link text-indigo-600 active transition-colors duration-300">Home</a>
                    <a href="about.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">About</a>
                    <a href="find_seat.php" class="nav-link text-gray-700 hover:text-indigo-600 transition-colors duration-300">Find Seat</a>
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
    <div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>

    <!-- Mobile Menu Sidebar -->
    <div id="mobile-menu-container" class="mobile-menu-container">
        <div class="p-6">
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-3">
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
                <a href="#home" class="mobile-nav-link block px-4 py-3 text-indigo-600 bg-indigo-50 rounded-xl transition-colors duration-200 flex items-center">
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
    <section id="home" class="relative gradient-bg py-20 md:py-32">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative max-w-6xl mx-auto px-6 text-center text-white">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up"><?= htmlspecialchars($settings['site_title']); ?></h1>
            <p class="text-lg md:text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150"><?= htmlspecialchars($settings['site_description']); ?></p>
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="zoom-in" data-aos-delay="300">
                <a href="#find" class="bg-white text-indigo-600 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold shadow-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Find Your Seat
                </a>
                <a href="#notices" class="bg-transparent border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition duration-300 flex items-center justify-center">
                    <i class="fas fa-bullhorn mr-2"></i> View Notices
                </a>
            </div>
        </div>
    </section>

    <!-- Find Your Seat Section -->
    <section id="find" class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="max-w-4xl mx-auto text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Find Your Exam Seat</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Enter your details below to find your assigned exam seat and room information.</p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl p-6 md:p-8 max-w-3xl mx-auto card-hover" data-aos="fade-up" data-aos-delay="100">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-building mr-2 text-indigo-500"></i>Department</label>
                        <select name="department" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Department</option>
                            <?php
                            $departments->data_seek(0); // Reset pointer
                            while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($dept['dept_name']); ?>"><?= htmlspecialchars($dept['dept_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>Session</label>
                        <select name="session" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Session</option>
                            <option>2020-21</option>
                            <option>2021-22</option>
                            <option>2022-23</option>
                            <option>2023-24</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-graduation-cap mr-2 text-indigo-500"></i>Semester</label>
                        <select name="semester" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Semester</option>
                            <option>1st</option>
                            <option>2nd</option>
                            <option>3rd</option>
                            <option>4th</option>
                            <option>5th</option>
                            <option>6th</option>
                            <option>7th</option>
                            <option>8th</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-file-alt mr-2 text-indigo-500"></i>Exam Type</label>
                        <select name="exam_type" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">Select Exam Type</option>
                            <option>Mid-Term</option>
                            <option>Final</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 font-medium text-gray-700"><i class="fas fa-id-card mr-2 text-indigo-500"></i>Roll Number</label>
                        <input type="text" name="roll" placeholder="Enter Your Roll Number" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div class="md:col-span-2 text-center pt-2">
                        <button type="submit" name="find_seat" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3 rounded-lg font-semibold transition flex items-center justify-center mx-auto shadow-lg">
                            <i class="fas fa-search mr-2"></i> Find My Seat
                        </button>
                    </div>
                </form>
            </div>

            <!-- Seat Info Modal -->
            <div id="seatModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 relative transform transition-all duration-300 scale-95" id="modalContainer">
                    <button id="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl font-bold bg-gray-100 hover:bg-gray-200 w-8 h-8 rounded-full flex items-center justify-center transition">&times;</button>
                    <div id="modalContent" class="text-center pt-2">
                        <!-- Seat info will be inserted here dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">About The System</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">
                    Our Seat Plan Management System helps educational institutions efficiently organize exam seating arrangements across multiple rooms and departments.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 gradient-bg rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-chair text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Seat Allocation</h3>
                    <p class="text-gray-600">Automated and optimized seat allocation for students based on various parameters to ensure fair distribution.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 gradient-bg rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Room Management</h3>
                    <p class="text-gray-600">Efficiently manage multiple exam rooms with capacity tracking and seating arrangement visualization.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 gradient-bg rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-bullhorn text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Notice Board</h3>
                    <p class="text-gray-600">Centralized notice system to keep students informed about exam schedules and important announcements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Notices Section -->
    <section id="notices" class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Latest Notices</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Stay updated with the latest exam schedules, announcements, and institutional notices.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $notices->data_seek(0); // Reset pointer
                $notice_count = 0;
                while ($row = $notices->fetch_assoc()):
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
                <a href="#" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                    View All Notices <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Departments Section -->
    <section id="departments" class="py-16 md:py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Departments</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Explore the various academic departments and their contact information.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $departments->data_seek(0); // Reset pointer
                $dept_count = 0;
                while ($dept = $departments->fetch_assoc()):
                    $dept_count++;
                    $delay = ($dept_count % 3) * 100;
                ?>
                    <div class="bg-white p-6 rounded-xl shadow-md card-hover" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
                        <div class="w-14 h-14 gradient-bg rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-university text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($dept['dept_name']); ?></h3>
                        <div class="space-y-2 mt-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-user-tie mr-3 text-indigo-500"></i>
                                <span>Head: <?= htmlspecialchars($dept['head_name']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope mr-3 text-indigo-500"></i>
                                <span><?= htmlspecialchars($dept['contact_email']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 md:py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Contact Us</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">Have any queries or feedback? We'd love to hear from you.</p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl p-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Get In Touch</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-envelope text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-700">Email</h4>
                                    <p class="text-gray-600"><?= htmlspecialchars($settings['contact_email']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-phone text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-700">Phone</h4>
                                    <p class="text-gray-600"><?= htmlspecialchars($settings['contact_phone']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-map-marker-alt text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-700">Address</h4>
                                    <p class="text-gray-600">Educational Institution Campus, City, Country</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Send a Message</h3>
                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                                <input type="text" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Enter your name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Email</label>
                                <input type="email" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Enter your email">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea rows="3" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Enter your message"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-medium transition">Send Message</button>
                        </form>
                    </div>
                </div>
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
                        <li><a href="#home" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white transition">About</a></li>
                        <li><a href="#find" class="text-gray-400 hover:text-white transition">Find Seat</a></li>
                        <li><a href="#notices" class="text-gray-400 hover:text-white transition">Notices</a></li>
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

        // Modal functionality
        const modal = document.getElementById('seatModal');
        const modalContent = document.getElementById('modalContent');
        const closeModal = document.getElementById('closeModal');
        const modalContainer = document.getElementById('modalContainer');

        closeModal.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Close modal when clicking outside
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });

        <?php if ($show_modal): ?>
            let content = '';
            <?php if ($seat_info): ?>
                content += `<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check text-green-600 text-2xl"></i>
                            </div>`;
                content += `<h3 class="text-xl font-bold text-gray-800 mb-2">Seat Found!</h3>`;
                content += `<div class="bg-gray-50 rounded-lg p-4 mb-4 text-left">`;
                content += `<div class="flex justify-between mb-2"><span class="font-medium">Name:</span> <span><?= addslashes($seat_info['name']); ?></span></div>`;
                content += `<div class="flex justify-between mb-2"><span class="font-medium">Roll No:</span> <span><?= addslashes($seat_info['roll_no']); ?></span></div>`;
                content += `<div class="flex justify-between mb-2"><span class="font-medium">Department:</span> <span><?= addslashes($seat_info['department']); ?></span></div>`;
                content += `<div class="flex justify-between mb-2"><span class="font-medium">Semester:</span> <span><?= addslashes($seat_info['semester']); ?></span></div>`;
                content += `<div class="flex justify-between mb-2"><span class="font-medium">Room:</span> <span class="font-semibold text-indigo-600"><?= addslashes($seat_info['room_name'] ?? 'Not assigned'); ?></span></div>`;
                content += `<div class="flex justify-between"><span class="font-medium">Seat Number:</span> <span class="font-semibold text-indigo-600"><?= addslashes($seat_info['seat_number'] ?? 'Not assigned'); ?></span></div>`;
                content += `</div>`;
                content += `<p class="text-green-600 font-medium mb-4"><i class="fas fa-info-circle mr-1"></i> Please arrive at your exam room 15 minutes early.</p>`;
                content += `<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition">Print Details</button>`;
            <?php else: ?>
                content += `<div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-times text-red-600 text-2xl"></i>
                            </div>`;
                content += `<h3 class="text-xl font-bold text-gray-800 mb-2">No Seat Found</h3>`;
                content += `<p class="text-gray-600 mb-4">We couldn't find a seat allocation for the provided details.</p>`;
                content += `<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left mb-4">`;
                content += `<p class="text-yellow-700 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Please verify your information or contact your department.</p>`;
                content += `</div>`;
                content += `<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition">Try Again</button>`;
            <?php endif; ?>
            modalContent.innerHTML = content;
            modal.classList.remove('hidden');
            // Animate modal appearance
            setTimeout(() => {
                modalContainer.classList.remove('scale-95');
                modalContainer.classList.add('scale-100');
            }, 10);
        <?php endif; ?>
    </script>
</body>

</html>

<?php $conn->close(); ?>