<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "seatplan_management";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Fetch site settings
$settings = $conn->query("SELECT * FROM site_settings LIMIT 1")->fetch_assoc();

// Fetch all notices
$all_notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC");

// Fetch student's seat allocation details
$student_id = $_SESSION['student_id'];
$seat_query = "SELECT sa.*, r.room_name, r.building_name, r.capacity 
               FROM seat_allocations sa 
               JOIN rooms r ON sa.room_id = r.room_id 
               WHERE sa.student_id = $student_id";
$seat_result = $conn->query($seat_query);
$seat_data = $seat_result->fetch_assoc();

// Fetch room layout for seat plan
$room_layout_query = "SELECT * FROM rooms WHERE room_id = " . ($seat_data['room_id'] ?? 1);
$room_layout_result = $conn->query($room_layout_query);
$room_layout = $room_layout_result->fetch_assoc();

// Process logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Process profile update
if (isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $update_sql = "UPDATE students SET name='$name', email='$email', phone='$phone' WHERE student_id=$student_id";
    if ($conn->query($update_sql)) {
        $_SESSION['student_name'] = $name;
        $_SESSION['student_email'] = $email;
        $_SESSION['student_phone'] = $phone;
        $profile_success = "Profile updated successfully!";
    } else {
        $profile_error = "Error updating profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?= htmlspecialchars($settings['site_title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/906/906175.png">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }
        
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .nav-item {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }
        
        .seat {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .seat.available {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }
        
        .seat.occupied {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        
        .seat.current {
            background: #dbeafe;
            border-color: var(--primary);
            color: var(--primary);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .document-card {
            transition: all 0.3s ease;
        }
        
        .document-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Mobile sidebar styles */
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            z-index: 50;
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 1023px) {
            .main-content {
                margin-left: 0;
            }
            
            .seat {
                width: 45px;
                height: 45px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            
            .grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .seat {
                width: 40px;
                height: 40px;
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 480px) {
            .grid-cols-4 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .p-8 {
                padding: 1rem;
            }
            
            .text-2xl {
                font-size: 1.5rem;
            }
            
            .seat {
                width: 35px;
                height: 35px;
                font-size: 0.6rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobile-menu-button" class="bg-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
            <i class="fas fa-bars text-gray-700"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 w-80 bg-white shadow-2xl lg:translate-x-0">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chair text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($settings['site_title']); ?></h1>
                    <p class="text-gray-600 text-sm">Student Portal</p>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-14 h-14 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-graduate text-white text-xl"></i>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full border-2 border-white"></div>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['student_name']); ?></h3>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($_SESSION['student_department']); ?></p>
                    <p class="text-gray-500 text-xs"><?= htmlspecialchars($_SESSION['student_roll_no']); ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-2">
            <a href="#" class="nav-item active flex items-center space-x-4 p-4 rounded-xl transition" data-tab="dashboard">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-indigo-600"></i>
                </div>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="#" class="nav-item flex items-center space-x-4 p-4 text-gray-600 hover:bg-gray-50 rounded-xl transition" data-tab="seat-plan">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chair text-gray-600"></i>
                </div>
                <span class="font-medium">Seat Plan</span>
            </a>
            
            <a href="#" class="nav-item flex items-center space-x-4 p-4 text-gray-600 hover:bg-gray-50 rounded-xl transition" data-tab="notices">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bullhorn text-gray-600"></i>
                </div>
                <span class="font-medium">Notices</span>
                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full" id="notice-count"><?= $all_notices->num_rows; ?></span>
            </a>
            
            <a href="#" class="nav-item flex items-center space-x-4 p-4 text-gray-600 hover:bg-gray-50 rounded-xl transition" data-tab="profile">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-gray-600"></i>
                </div>
                <span class="font-medium">Profile</span>
            </a>
            
            <a href="#" class="nav-item flex items-center space-x-4 p-4 text-gray-600 hover:bg-gray-50 rounded-xl transition" data-tab="documents">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-600"></i>
                </div>
                <span class="font-medium">Documents</span>
            </a>
        </nav>

        <!-- Quick Stats -->
        <div class="p-4 mt-4">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-4 text-white">
                <h4 class="font-semibold text-sm mb-2">Academic Progress</h4>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold"><?= htmlspecialchars($_SESSION['student_semester']); ?></p>
                        <p class="text-indigo-100 text-xs">Current Semester</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100">
            <a href="?logout=true" class="flex items-center space-x-3 p-3 text-red-600 hover:bg-red-50 rounded-xl transition">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span class="font-medium">Logout</span>
            </a>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

    <!-- Main Content -->
    <div class="main-content lg:ml-80">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
            <div class="px-4 md:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-800" id="page-title">Dashboard Overview</h1>
                        <p class="text-gray-600 text-sm" id="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['student_name']); ?>! 👋</p>
                    </div>
                    <div class="flex items-center space-x-4 md:space-x-6">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-gray-200 transition">
                                <i class="fas fa-bell text-gray-600"></i>
                            </button>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"><?= $all_notices->num_rows; ?></span>
                        </div>
                        
                        <!-- Current Tab Quick Actions -->
                        <div class="hidden md:flex items-center space-x-3" id="quick-actions">
                            <button class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition" data-tab="seat-plan">
                                <i class="fas fa-chair mr-2"></i>Seat Plan
                            </button>
                            <button class="bg-purple-50 text-purple-600 px-4 py-2 rounded-xl font-medium hover:bg-purple-100 transition" data-tab="notices">
                                <i class="fas fa-bullhorn mr-2"></i>Notices
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area - All Tabs -->
        <main class="p-4 md:p-8">
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content active">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
                    <!-- Academic Status -->
                    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Academic Status</p>
                                <p class="text-xl md:text-2xl font-bold text-gray-800">Active</p>
                            </div>
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-lg md:text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>Good Standing</span>
                        </div>
                    </div>

                    <!-- Current Semester -->
                    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Current Semester</p>
                                <p class="text-xl md:text-2xl font-bold text-gray-800"><?= htmlspecialchars($_SESSION['student_semester']); ?></p>
                            </div>
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-600 text-lg md:text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-clock mr-1"></i>
                            <span>Session: <?= htmlspecialchars($_SESSION['student_session']); ?></span>
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Department</p>
                                <p class="text-lg md:text-xl font-bold text-gray-800 truncate"><?= htmlspecialchars($_SESSION['student_department']); ?></p>
                            </div>
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-university text-purple-600 text-lg md:text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-purple-600">
                            <i class="fas fa-code-branch mr-1"></i>
                            <span>Computer Science</span>
                        </div>
                    </div>

                    <!-- Seat Status -->
                    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Seat Status</p>
                                <p class="text-xl md:text-2xl font-bold text-gray-800">
                                    <?= $_SESSION['student_seat_number'] ? 'Allocated' : 'Pending'; ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 md:w-12 md:h-12 <?= $_SESSION['student_seat_number'] ? 'bg-green-100' : 'bg-orange-100'; ?> rounded-xl flex items-center justify-center">
                                <i class="fas fa-chair <?= $_SESSION['student_seat_number'] ? 'text-green-600' : 'text-orange-600'; ?> text-lg md:text-xl"></i>
                            </div>
                        </div>
                        <div class="flex items-center text-sm <?= $_SESSION['student_seat_number'] ? 'text-green-600' : 'text-orange-600'; ?>">
                            <i class="fas <?= $_SESSION['student_seat_number'] ? 'fa-check' : 'fa-clock'; ?> mr-1"></i>
                            <span><?= $_SESSION['student_seat_number'] ? 'Ready for Exam' : 'Awaiting Allocation'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8">
                    <!-- Left Column -->
                    <div class="xl:col-span-2 space-y-6 md:space-y-8">
                        <!-- Welcome Card -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl md:rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
                            <div class="relative z-10">
                                <h2 class="text-xl md:text-2xl font-bold mb-2">Hello, <?= htmlspecialchars($_SESSION['student_name']); ?>! 🎓</h2>
                                <p class="text-indigo-100 mb-4 md:mb-6 max-w-md">Welcome to your student dashboard. Here you can find all your academic information, exam details, and important updates.</p>
                                <div class="flex flex-wrap gap-2 md:gap-3">
                                    <button class="bg-white text-indigo-600 px-4 md:px-6 py-2 md:py-3 rounded-xl font-semibold hover:bg-gray-100 transition flex items-center text-sm md:text-base" data-tab="seat-plan">
                                        <i class="fas fa-chair mr-2"></i> View Seat Plan
                                    </button>
                                    <button class="bg-white/20 text-white px-4 md:px-6 py-2 md:py-3 rounded-xl font-semibold hover:bg-white/30 transition flex items-center text-sm md:text-base" data-tab="notices">
                                        <i class="fas fa-bullhorn mr-2"></i> Check Notices
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Floating Elements -->
                            <div class="absolute top-4 right-4 floating hidden md:block">
                                <div class="w-16 h-16 md:w-20 md:h-20 bg-white/10 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-white text-xl md:text-2xl"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-4 right-4 md:right-20 floating hidden md:block" style="animation-delay: 1s;">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-white/10 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-book text-white text-lg md:text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Exam Seat Information -->
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover">
                            <div class="flex items-center justify-between mb-4 md:mb-6">
                                <h2 class="text-lg md:text-xl font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-chair mr-3 text-green-600"></i>
                                    Exam Seat Information
                                </h2>
                                <span class="bg-green-100 text-green-800 text-xs md:text-sm font-medium px-2 md:px-3 py-1 rounded-full">
                                    <?= $_SESSION['student_seat_number'] ? 'Allocated' : 'Pending'; ?>
                                </span>
                            </div>

                            <?php if ($_SESSION['student_seat_number']): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                    <!-- Seat Details -->
                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-4 md:p-6">
                                        <div class="text-center">
                                            <div class="w-12 h-12 md:w-16 md:h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-check text-green-600 text-xl md:text-2xl"></i>
                                            </div>
                                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4">Seat Allocated! 🎉</h3>
                                            
                                            <div class="space-y-3 md:space-y-4 mb-4 md:mb-6">
                                                <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                            <i class="fas fa-hashtag text-green-600 text-sm md:text-base"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs md:text-sm text-gray-600">Seat Number</p>
                                                            <p class="font-bold text-gray-800 text-base md:text-lg"><?= htmlspecialchars($_SESSION['student_seat_number']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                            <i class="fas fa-door-open text-blue-600 text-sm md:text-base"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs md:text-sm text-gray-600">Room</p>
                                                            <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_room_name']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                            <i class="fas fa-building text-purple-600 text-sm md:text-base"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs md:text-sm text-gray-600">Building</p>
                                                            <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_building_name']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Exam Instructions -->
                                    <div class="space-y-3 md:space-y-4">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 md:p-5">
                                            <h4 class="font-semibold text-yellow-800 mb-2 md:mb-3 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-2"></i>
                                                Important Instructions
                                            </h4>
                                            <ul class="text-yellow-700 text-xs md:text-sm space-y-1 md:space-y-2">
                                                <li class="flex items-start">
                                                    <i class="fas fa-clock mt-1 mr-2 text-yellow-600"></i>
                                                    <span>Arrive at least 15 minutes before exam time</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-id-card mt-1 mr-2 text-yellow-600"></i>
                                                    <span>Bring your student ID card and admit card</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-map-marker-alt mt-1 mr-2 text-yellow-600"></i>
                                                    <span>Follow the seating arrangement strictly</span>
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-user-shield mt-1 mr-2 text-yellow-600"></i>
                                                    <span>Contact invigilator for any issues</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-200 rounded-2xl p-6 md:p-8 text-center">
                                    <div class="w-16 h-16 md:w-20 md:h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-clock text-orange-600 text-xl md:text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Seat Not Allocated Yet</h3>
                                    <p class="text-gray-600 mb-4 md:mb-6">Your exam seat allocation is pending. Please check back later for updates.</p>
                                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 md:px-8 py-2 md:py-3 rounded-xl font-semibold transition flex items-center mx-auto text-sm md:text-base">
                                        <i class="fas fa-sync-alt mr-2"></i> Check Again
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6 md:space-y-8">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover">
                            <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                <i class="fas fa-bolt mr-3 text-purple-600"></i>
                                Quick Actions
                            </h2>
                            <div class="space-y-2 md:space-y-3">
                                <button class="w-full flex items-center justify-between p-3 md:p-4 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition group" data-tab="seat-plan">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 group-hover:bg-white transition">
                                            <i class="fas fa-chair text-purple-600 text-sm md:text-base"></i>
                                        </div>
                                        <span class="font-medium text-sm md:text-base">View Seat Plan</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-purple-400 group-hover:translate-x-1 transition"></i>
                                </button>
                                
                                <button class="w-full flex items-center justify-between p-3 md:p-4 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition group" data-tab="notices">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 group-hover:bg-white transition">
                                            <i class="fas fa-bullhorn text-blue-600 text-sm md:text-base"></i>
                                        </div>
                                        <span class="font-medium text-sm md:text-base">Check Notices</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-blue-400 group-hover:translate-x-1 transition"></i>
                                </button>
                                
                                <button class="w-full flex items-center justify-between p-3 md:p-4 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition group" data-tab="profile">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 group-hover:bg-white transition">
                                            <i class="fas fa-user text-green-600 text-sm md:text-base"></i>
                                        </div>
                                        <span class="font-medium text-sm md:text-base">Update Profile</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-green-400 group-hover:translate-x-1 transition"></i>
                                </button>
                                
                                <button class="w-full flex items-center justify-between p-3 md:p-4 bg-orange-50 text-orange-700 rounded-xl hover:bg-orange-100 transition group" data-tab="documents">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 group-hover:bg-white transition">
                                            <i class="fas fa-file-alt text-orange-600 text-sm md:text-base"></i>
                                        </div>
                                        <span class="font-medium text-sm md:text-base">My Documents</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-orange-400 group-hover:translate-x-1 transition"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Recent Notices -->
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <h2 class="text-lg md:text-xl font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-bullhorn mr-3 text-red-600"></i>
                                    Recent Notices
                                </h2>
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">New</span>
                            </div>
                            <div class="space-y-3 md:space-y-4">
                                <?php 
                                $recent_notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC LIMIT 3");
                                if ($recent_notices->num_rows > 0): 
                                    while ($notice = $recent_notices->fetch_assoc()): 
                                ?>
                                    <div class="border-l-4 border-red-500 pl-3 md:pl-4 py-2 md:py-3 hover:bg-gray-50 rounded-r-xl transition">
                                        <h3 class="font-semibold text-gray-800 text-sm mb-1"><?= htmlspecialchars($notice['title']); ?></h3>
                                        <p class="text-gray-600 text-xs mb-2 line-clamp-2"><?= htmlspecialchars($notice['description']); ?></p>
                                        <div class="flex items-center text-gray-500 text-xs">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span><?= date('M j, Y g:i A', strtotime($notice['posted_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                    <p class="text-gray-500 text-sm text-center py-4">No notices available at the moment.</p>
                                <?php endif; ?>
                                <button class="w-full text-center text-indigo-600 hover:text-indigo-800 font-medium text-sm mt-3 md:mt-4 transition" data-tab="notices">
                                    View All Notices <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Plan Tab -->
            <div id="seat-plan-tab" class="tab-content">
                <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover mb-6 md:mb-8">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-chair mr-3 text-indigo-600"></i>
                        Exam Seat Plan
                    </h2>
                    <p class="text-gray-600 mb-4 md:mb-6">View your allocated seat and room layout for the upcoming exams.</p>
                    
                    <?php if ($_SESSION['student_seat_number']): ?>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                            <!-- Seat Details -->
                            <div class="lg:col-span-1">
                                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-2xl p-4 md:p-6">
                                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                                        Your Seat Details
                                    </h3>
                                    <div class="space-y-3 md:space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 md:w-10 md:h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                    <i class="fas fa-hashtag text-indigo-600 text-sm md:text-base"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm text-gray-600">Seat Number</p>
                                                    <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_seat_number']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                    <i class="fas fa-door-open text-blue-600 text-sm md:text-base"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm text-gray-600">Room</p>
                                                    <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_room_name']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                    <i class="fas fa-building text-purple-600 text-sm md:text-base"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm text-gray-600">Building</p>
                                                    <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_building_name']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                                                    <i class="fas fa-users text-green-600 text-sm md:text-base"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs md:text-sm text-gray-600">Capacity</p>
                                                    <p class="font-bold text-gray-800 text-sm md:text-base"><?= htmlspecialchars($room_layout['capacity'] ?? 'N/A'); ?> Students</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instructions -->
                                <div class="mt-4 md:mt-6 bg-yellow-50 border border-yellow-200 rounded-2xl p-4 md:p-5">
                                    <h4 class="font-semibold text-yellow-800 mb-2 md:mb-3 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        Important Notes
                                    </h4>
                                    <ul class="text-yellow-700 text-xs md:text-sm space-y-1 md:space-y-2">
                                        <li class="flex items-start">
                                            <i class="fas fa-map-marker-alt mt-1 mr-2 text-yellow-600"></i>
                                            <span>Locate your seat before the exam day</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-clock mt-1 mr-2 text-yellow-600"></i>
                                            <span>Arrive 15 minutes early to find your seat</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-id-card mt-1 mr-2 text-yellow-600"></i>
                                            <span>Bring your student ID for verification</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Room Layout -->
                            <div class="lg:col-span-2">
                                <div class="bg-white border border-gray-200 rounded-2xl p-4 md:p-6">
                                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                        <i class="fas fa-map mr-2 text-blue-600"></i>
                                        Room Layout - <?= htmlspecialchars($_SESSION['student_room_name']); ?>
                                    </h3>
                                    <p class="text-gray-600 mb-4 md:mb-6">Your seat is highlighted in blue. Each number represents a seat in the examination room.</p>
                                    
                                    <div class="bg-gray-50 rounded-xl p-4 md:p-6">
                                        <div class="text-center mb-4 md:mb-6">
                                            <div class="w-12 h-6 md:w-16 md:h-8 bg-gray-300 rounded-lg mx-auto mb-2"></div>
                                            <p class="text-xs md:text-sm text-gray-600">Teacher's Desk</p>
                                        </div>
                                        
                                        <div class="grid grid-cols-4 md:grid-cols-5 gap-3 md:gap-4 mx-auto max-w-xs md:max-w-md">
                                            <?php
                                            // Generate sample seat layout
                                            $total_seats = $room_layout['capacity'] ?? 50;
                                            $rows = ceil($total_seats / 5);
                                            $current_seat = $_SESSION['student_seat_number'];
                                            
                                            for ($i = 1; $i <= $total_seats; $i++):
                                                $seat_class = "available";
                                                $seat_number = "A-" . $i;
                                                
                                                if ($seat_number === $current_seat) {
                                                    $seat_class = "current";
                                                } elseif ($i % 7 === 0 || $i % 13 === 0) {
                                                    $seat_class = "occupied";
                                                }
                                            ?>
                                                <div class="seat <?= $seat_class; ?>">
                                                    <?= $seat_number; ?>
                                                </div>
                                            <?php endfor; ?>
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
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-200 rounded-2xl p-6 md:p-8 text-center">
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-clock text-orange-600 text-xl md:text-2xl"></i>
                            </div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Seat Plan Not Available</h3>
                            <p class="text-gray-600 mb-4 md:mb-6">Your exam seat allocation is pending. The seat plan will be available once your seat is allocated.</p>
                            <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 md:px-8 py-2 md:py-3 rounded-xl font-semibold transition flex items-center mx-auto text-sm md:text-base">
                                <i class="fas fa-sync-alt mr-2"></i> Check Again
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notices Tab -->
            <div id="notices-tab" class="tab-content">
                <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover mb-6 md:mb-8">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-bullhorn mr-3 text-red-600"></i>
                        Important Notices & Announcements
                    </h2>
                    <p class="text-gray-600 mb-4 md:mb-6">Stay updated with the latest announcements from the administration.</p>
                    
                    <div class="space-y-4 md:space-y-6">
                        <?php if ($all_notices->num_rows > 0): 
                            while ($notice = $all_notices->fetch_assoc()): 
                                $is_new = (time() - strtotime($notice['posted_at'])) < (7 * 24 * 60 * 60); // New if posted within 7 days
                        ?>
                            <div class="border border-gray-200 rounded-2xl p-4 md:p-6 hover:border-red-200 hover:bg-red-50 transition-all duration-300">
                                <div class="flex items-start justify-between mb-2 md:mb-3">
                                    <h3 class="text-base md:text-lg font-bold text-gray-800 flex items-center">
                                        <?= htmlspecialchars($notice['title']); ?>
                                        <?php if ($is_new): ?>
                                            <span class="ml-2 md:ml-3 bg-red-500 text-white text-xs px-2 py-1 rounded-full">New</span>
                                        <?php endif; ?>
                                    </h3>
                                    <span class="text-xs md:text-sm text-gray-500 bg-gray-100 px-2 md:px-3 py-1 rounded-full">
                                        <?= date('M j, Y', strtotime($notice['posted_at'])); ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-3 md:mb-4 text-sm md:text-base"><?= htmlspecialchars($notice['description']); ?></p>
                                <div class="flex items-center text-xs md:text-sm text-gray-500">
                                    <i class="fas fa-clock mr-2"></i>
                                    <span>Posted on <?= date('F j, Y \\a\\t g:i A', strtotime($notice['posted_at'])); ?></span>
                                </div>
                            </div>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <div class="text-center py-8 md:py-12">
                                <div class="w-16 h-16 md:w-20 md:h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-bullhorn text-gray-400 text-xl md:text-2xl"></i>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">No Notices Available</h3>
                                <p class="text-gray-600 text-sm md:text-base">There are no notices at the moment. Please check back later.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content">
                <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover mb-6 md:mb-8">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-user mr-3 text-green-600"></i>
                        Student Profile
                    </h2>
                    <p class="text-gray-600 mb-4 md:mb-6">Manage your personal information and account settings.</p>
                    
                    <?php if (isset($profile_success)): ?>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-3 md:p-4 mb-4 md:mb-6">
                            <div class="flex items-center">
                                <div class="w-6 h-6 md:w-8 md:h-8 bg-green-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-check text-green-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-green-700 text-sm md:text-base"><?= $profile_success; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($profile_error)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 md:p-4 mb-4 md:mb-6">
                            <div class="flex items-center">
                                <div class="w-6 h-6 md:w-8 md:h-8 bg-red-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-red-700 text-sm md:text-base"><?= $profile_error; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                        <!-- Profile Information -->
                        <div class="lg:col-span-2">
                            <form method="POST">
                                <div class="bg-gray-50 rounded-2xl p-4 md:p-6">
                                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                        Personal Information
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                            <input type="text" name="name" value="<?= htmlspecialchars($_SESSION['student_name']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm md:text-base" required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                            <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['student_email']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm md:text-base" required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                            <input type="tel" name="phone" value="<?= htmlspecialchars($_SESSION['student_phone']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm md:text-base">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                            <input type="text" value="<?= htmlspecialchars($_SESSION['student_department']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-100 border border-gray-300 rounded-lg text-sm md:text-base" disabled>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Roll Number</label>
                                            <input type="text" value="<?= htmlspecialchars($_SESSION['student_roll_no']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-100 border border-gray-300 rounded-lg text-sm md:text-base" disabled>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                                            <input type="text" value="<?= htmlspecialchars($_SESSION['student_registration_no']); ?>" 
                                                   class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-100 border border-gray-300 rounded-lg text-sm md:text-base" disabled>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 md:mt-6">
                                        <button type="submit" name="update_profile" class="bg-blue-500 hover:bg-blue-600 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg font-semibold transition flex items-center text-sm md:text-base">
                                            <i class="fas fa-save mr-2"></i> Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Account Summary -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-4 md:p-6">
                                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                    <i class="fas fa-user-shield mr-2 text-green-600"></i>
                                    Account Summary
                                </h3>
                                <div class="space-y-3 md:space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Member Since</span>
                                        <span class="font-medium text-gray-800 text-sm md:text-base"><?= date('M Y', strtotime($_SESSION['student_session'])); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Account Status</span>
                                        <span class="bg-green-100 text-green-800 text-xs md:text-sm font-medium px-2 py-1 rounded-full">Active</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Last Login</span>
                                        <span class="font-medium text-gray-800 text-sm md:text-base">Today</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-4 md:p-6">
                                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4 flex items-center">
                                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>
                                    Academic Summary
                                </h3>
                                <div class="space-y-3 md:space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Current Semester</span>
                                        <span class="font-medium text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_semester']); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Session</span>
                                        <span class="font-medium text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_session']); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 text-sm md:text-base">Department</span>
                                        <span class="font-medium text-gray-800 text-sm md:text-base"><?= htmlspecialchars($_SESSION['student_department']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div id="documents-tab" class="tab-content">
                <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 card-hover mb-6 md:mb-8">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-file-alt mr-3 text-orange-600"></i>
                        My Documents
                    </h2>
                    <p class="text-gray-600 mb-4 md:mb-6">Access and download your important academic documents.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <!-- Admit Card -->
                        <div class="document-card bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-id-card text-blue-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">Available</span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Exam Admit Card</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Official admission card for semester examinations.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center">
                                    <i class="fas fa-download mr-1 md:mr-2"></i> Download
                                </button>
                                <button class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition">
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Fee Receipt -->
                        <div class="document-card bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-receipt text-green-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">Available</span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Fee Receipt</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Semester fee payment confirmation receipt.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center">
                                    <i class="fas fa-download mr-1 md:mr-2"></i> Download
                                </button>
                                <button class="bg-green-100 hover:bg-green-200 text-green-700 p-2 rounded-lg transition">
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- ID Card -->
                        <div class="document-card bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-address-card text-purple-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full">Available</span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Student ID Card</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Official student identification card.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-purple-500 hover:bg-purple-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center">
                                    <i class="fas fa-download mr-1 md:mr-2"></i> Download
                                </button>
                                <button class="bg-purple-100 hover:bg-purple-200 text-purple-700 p-2 rounded-lg transition">
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Academic Transcript -->
                        <div class="document-card bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-scroll text-orange-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full">Pending</span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Academic Transcript</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Official academic record and grades.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center" disabled>
                                    <i class="fas fa-clock mr-1 md:mr-2"></i> Processing
                                </button>
                                <button class="bg-orange-100 hover:bg-orange-200 text-orange-700 p-2 rounded-lg transition" disabled>
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Course Registration -->
                        <div class="document-card bg-gradient-to-br from-red-50 to-pink-50 border border-red-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-red-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">Available</span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Course Registration</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Current semester course registration form.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center">
                                    <i class="fas fa-download mr-1 md:mr-2"></i> Download
                                </button>
                                <button class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition">
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Seat Allocation -->
                        <div class="document-card bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-200 rounded-2xl p-4 md:p-6">
                            <div class="flex items-center justify-between mb-3 md:mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-chair text-teal-600 text-lg md:text-xl"></i>
                                </div>
                                <span class="bg-teal-100 text-teal-800 text-xs font-medium px-2 py-1 rounded-full">
                                    <?= $_SESSION['student_seat_number'] ? 'Available' : 'Pending'; ?>
                                </span>
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">Seat Allocation</h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-3 md:mb-4">Exam seat allocation details and room plan.</p>
                            <div class="flex space-x-2 md:space-x-3">
                                <button class="flex-1 bg-teal-500 hover:bg-teal-600 text-white py-2 px-3 md:px-4 rounded-lg text-xs md:text-sm font-medium transition flex items-center justify-center" <?= $_SESSION['student_seat_number'] ? '' : 'disabled'; ?>>
                                    <i class="fas fa-download mr-1 md:mr-2"></i> Download
                                </button>
                                <button class="bg-teal-100 hover:bg-teal-200 text-teal-700 p-2 rounded-lg transition" <?= $_SESSION['student_seat_number'] ? '' : 'disabled'; ?>>
                                    <i class="fas fa-eye text-xs md:text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6 md:py-8 mt-8 md:mt-12">
            <div class="max-w-7xl mx-auto px-4 md:px-8">
                <div class="text-center">
                    <div class="flex items-center justify-center space-x-2 md:space-x-3 mb-3 md:mb-4">
                        <i class="fas fa-chair text-indigo-400 text-lg md:text-xl"></i>
                        <h3 class="text-base md:text-lg font-bold"><?= htmlspecialchars($settings['site_title']); ?></h3>
                    </div>
                    <p class="text-gray-400 text-xs md:text-sm"><?= htmlspecialchars($settings['footer_text']); ?></p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Tab navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');
            const navItems = document.querySelectorAll('.nav-item');
            const pageTitle = document.getElementById('page-title');
            const pageSubtitle = document.getElementById('page-subtitle');
            const quickActions = document.getElementById('quick-actions');
            
            // Tab titles and subtitles
            const tabData = {
                'dashboard': {
                    title: 'Dashboard Overview',
                    subtitle: 'Welcome back, <?= htmlspecialchars($_SESSION['student_name']); ?>! 👋'
                },
                'seat-plan': {
                    title: 'Exam Seat Plan',
                    subtitle: 'View your allocated seat and room layout'
                },
                'notices': {
                    title: 'Important Notices',
                    subtitle: 'Stay updated with the latest announcements'
                },
                'profile': {
                    title: 'Student Profile',
                    subtitle: 'Manage your personal information'
                },
                'documents': {
                    title: 'My Documents',
                    subtitle: 'Access your academic documents'
                }
            };
            
            // Quick actions for each tab
            const quickActionsData = {
                'dashboard': `
                    <button class="bg-indigo-50 text-indigo-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition text-sm md:text-base" data-tab="seat-plan">
                        <i class="fas fa-chair mr-1 md:mr-2"></i>Seat Plan
                    </button>
                    <button class="bg-purple-50 text-purple-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-purple-100 transition text-sm md:text-base" data-tab="notices">
                        <i class="fas fa-bullhorn mr-1 md:mr-2"></i>Notices
                    </button>
                `,
                'seat-plan': `
                    <button class="bg-indigo-50 text-indigo-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition text-sm md:text-base" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt mr-1 md:mr-2"></i>Dashboard
                    </button>
                    <button class="bg-purple-50 text-purple-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-purple-100 transition text-sm md:text-base" data-tab="notices">
                        <i class="fas fa-bullhorn mr-1 md:mr-2"></i>Notices
                    </button>
                `,
                'notices': `
                    <button class="bg-indigo-50 text-indigo-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition text-sm md:text-base" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt mr-1 md:mr-2"></i>Dashboard
                    </button>
                    <button class="bg-green-50 text-green-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-green-100 transition text-sm md:text-base" data-tab="seat-plan">
                        <i class="fas fa-chair mr-1 md:mr-2"></i>Seat Plan
                    </button>
                `,
                'profile': `
                    <button class="bg-indigo-50 text-indigo-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition text-sm md:text-base" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt mr-1 md:mr-2"></i>Dashboard
                    </button>
                    <button class="bg-orange-50 text-orange-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-orange-100 transition text-sm md:text-base" data-tab="documents">
                        <i class="fas fa-file-alt mr-1 md:mr-2"></i>Documents
                    </button>
                `,
                'documents': `
                    <button class="bg-indigo-50 text-indigo-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-indigo-100 transition text-sm md:text-base" data-tab="dashboard">
                        <i class="fas fa-tachometer-alt mr-1 md:mr-2"></i>Dashboard
                    </button>
                    <button class="bg-green-50 text-green-600 px-3 md:px-4 py-2 rounded-xl font-medium hover:bg-green-100 transition text-sm md:text-base" data-tab="profile">
                        <i class="fas fa-user mr-1 md:mr-2"></i>Profile
                    </button>
                `
            };
            
            // Function to switch tabs
            function switchTab(tabName) {
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Remove active class from all nav items
                navItems.forEach(item => {
                    item.classList.remove('active');
                });
                
                // Show selected tab content
                document.getElementById(`${tabName}-tab`).classList.add('active');
                
                // Add active class to selected nav item
                document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
                
                // Update page title and subtitle
                if (tabData[tabName]) {
                    pageTitle.textContent = tabData[tabName].title;
                    pageSubtitle.textContent = tabData[tabName].subtitle;
                }
                
                // Update quick actions
                if (quickActions && quickActionsData[tabName]) {
                    quickActions.innerHTML = quickActionsData[tabName];
                    
                    // Add event listeners to new quick action buttons
                    quickActions.querySelectorAll('[data-tab]').forEach(button => {
                        button.addEventListener('click', function() {
                            switchTab(this.getAttribute('data-tab'));
                        });
                    });
                }
            }
            
            // Add click event listeners to all tab buttons
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });
            
            // Mobile menu functionality
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.querySelector('.sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            
            function openMobileMenu() {
                sidebar.classList.add('open');
                mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeMobileMenu() {
                sidebar.classList.remove('open');
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
            
            mobileMenuButton.addEventListener('click', openMobileMenu);
            mobileOverlay.addEventListener('click', closeMobileMenu);
            
            // Close menu when clicking on a link
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMobileMenu();
                }
            });
            
            // Add loading state to buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.innerHTML.includes('Check Again')) {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                        }, 2000);
                    }
                });
            });
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>