<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
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

// Fetch stats for dashboard
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$total_rooms = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'];
$total_departments = $conn->query("SELECT COUNT(*) as total FROM departments")->fetch_assoc()['total'];
$total_allocations = $conn->query("SELECT COUNT(*) as total FROM seat_allocations")->fetch_assoc()['total'];

// Fetch recent data
$recent_students = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT * FROM departments")->fetch_all(MYSQLI_ASSOC);
$rooms = $conn->query("SELECT * FROM rooms")->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
$success_message = $error_message = "";

// Add Student
if (isset($_POST['add_student'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $roll_no = $conn->real_escape_string($_POST['roll_no']);
    $registration_no = $conn->real_escape_string($_POST['registration_no']);
    $department = $conn->real_escape_string($_POST['department']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $session = $conn->real_escape_string($_POST['session']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $sql = "INSERT INTO students (name, roll_no, registration_no, department, semester, session, email, phone) 
            VALUES ('$name', '$roll_no', '$registration_no', '$department', '$semester', '$session', '$email', '$phone')";
    
    if ($conn->query($sql)) {
        $success_message = "Student added successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error adding student: " . $conn->error;
    }
}

// Edit Student
if (isset($_POST['edit_student'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $roll_no = $conn->real_escape_string($_POST['roll_no']);
    $registration_no = $conn->real_escape_string($_POST['registration_no']);
    $department = $conn->real_escape_string($_POST['department']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $session = $conn->real_escape_string($_POST['session']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $sql = "UPDATE students SET name='$name', roll_no='$roll_no', registration_no='$registration_no', 
            department='$department', semester='$semester', session='$session', email='$email', phone='$phone' 
            WHERE student_id='$student_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Student updated successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error updating student: " . $conn->error;
    }
}

// Delete Student
if (isset($_GET['delete_student'])) {
    $student_id = $conn->real_escape_string($_GET['delete_student']);
    
    $sql = "DELETE FROM students WHERE student_id='$student_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Student deleted successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error deleting student: " . $conn->error;
    }
}

// Add Room
if (isset($_POST['add_room'])) {
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $capacity = $conn->real_escape_string($_POST['capacity']);
    $building_name = $conn->real_escape_string($_POST['building_name']);
    $floor_number = $conn->real_escape_string($_POST['floor_number']);

    $sql = "INSERT INTO rooms (room_name, capacity, building_name, floor_number) 
            VALUES ('$room_name', '$capacity', '$building_name', '$floor_number')";
    
    if ($conn->query($sql)) {
        $success_message = "Room added successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error adding room: " . $conn->error;
    }
}

// Edit Room
if (isset($_POST['edit_room'])) {
    $room_id = $conn->real_escape_string($_POST['room_id']);
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $capacity = $conn->real_escape_string($_POST['capacity']);
    $building_name = $conn->real_escape_string($_POST['building_name']);
    $floor_number = $conn->real_escape_string($_POST['floor_number']);

    $sql = "UPDATE rooms SET room_name='$room_name', capacity='$capacity', 
            building_name='$building_name', floor_number='$floor_number' WHERE room_id='$room_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Room updated successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error updating room: " . $conn->error;
    }
}

// Delete Room
if (isset($_GET['delete_room'])) {
    $room_id = $conn->real_escape_string($_GET['delete_room']);
    
    $sql = "DELETE FROM rooms WHERE room_id='$room_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Room deleted successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error deleting room: " . $conn->error;
    }
}

// Add Department
if (isset($_POST['add_department'])) {
    $dept_name = $conn->real_escape_string($_POST['dept_name']);
    $head_name = $conn->real_escape_string($_POST['head_name']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);

    $sql = "INSERT INTO departments (dept_name, head_name, contact_email) 
            VALUES ('$dept_name', '$head_name', '$contact_email')";
    
    if ($conn->query($sql)) {
        $success_message = "Department added successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error adding department: " . $conn->error;
    }
}

// Edit Department
if (isset($_POST['edit_department'])) {
    $dept_id = $conn->real_escape_string($_POST['dept_id']);
    $dept_name = $conn->real_escape_string($_POST['dept_name']);
    $head_name = $conn->real_escape_string($_POST['head_name']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);

    $sql = "UPDATE departments SET dept_name='$dept_name', head_name='$head_name', 
            contact_email='$contact_email' WHERE dept_id='$dept_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Department updated successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error updating department: " . $conn->error;
    }
}

// Delete Department
if (isset($_GET['delete_department'])) {
    $dept_id = $conn->real_escape_string($_GET['delete_department']);
    
    $sql = "DELETE FROM departments WHERE dept_id='$dept_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Department deleted successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error deleting department: " . $conn->error;
    }
}

// Add Notice
if (isset($_POST['add_notice'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "INSERT INTO notices (title, description) VALUES ('$title', '$description')";
    
    if ($conn->query($sql)) {
        $success_message = "Notice added successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error adding notice: " . $conn->error;
    }
}

// Edit Notice
if (isset($_POST['edit_notice'])) {
    $notice_id = $conn->real_escape_string($_POST['notice_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "UPDATE notices SET title='$title', description='$description' WHERE notice_id='$notice_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Notice updated successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error updating notice: " . $conn->error;
    }
}

// Delete Notice
if (isset($_GET['delete_notice'])) {
    $notice_id = $conn->real_escape_string($_GET['delete_notice']);
    
    $sql = "DELETE FROM notices WHERE notice_id='$notice_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Notice deleted successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error deleting notice: " . $conn->error;
    }
}

// Allocate Seat
if (isset($_POST['allocate_seat'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $room_id = $conn->real_escape_string($_POST['room_id']);
    $seat_number = $conn->real_escape_string($_POST['seat_number']);

    // Check if student already has allocation
    $check_sql = "SELECT * FROM seat_allocations WHERE student_id = '$student_id'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        $error_message = "Student already has a seat allocation!";
    } else {
        $sql = "INSERT INTO seat_allocations (student_id, room_id, seat_number) 
                VALUES ('$student_id', '$room_id', '$seat_number')";
        
        if ($conn->query($sql)) {
            $success_message = "Seat allocated successfully!";
            header("Refresh:2");
        } else {
            $error_message = "Error allocating seat: " . $conn->error;
        }
    }
}

// Delete Allocation
if (isset($_GET['delete_allocation'])) {
    $allocation_id = $conn->real_escape_string($_GET['delete_allocation']);
    
    $sql = "DELETE FROM seat_allocations WHERE allocation_id='$allocation_id'";
    
    if ($conn->query($sql)) {
        $success_message = "Seat allocation deleted successfully!";
        header("Refresh:2");
    } else {
        $error_message = "Error deleting seat allocation: " . $conn->error;
    }
}

// Fetch students and rooms for allocation form
$all_students = $conn->query("SELECT * FROM students")->fetch_all(MYSQLI_ASSOC);
$all_rooms = $conn->query("SELECT * FROM rooms")->fetch_all(MYSQLI_ASSOC);

// Fetch all allocations with student and room details
$allocations = $conn->query("
    SELECT sa.*, s.name, s.roll_no, r.room_name, r.building_name 
    FROM seat_allocations sa 
    JOIN students s ON sa.student_id = s.student_id 
    JOIN rooms r ON sa.room_id = r.room_id
    ORDER BY sa.allocated_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Process logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($settings['site_title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/906/906175.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sidebar {
            transition: all 0.3s ease;
            background: linear-gradient(180deg, var(--dark) 0%, #111827 100%);
        }
        
        .main-content {
            transition: all 0.3s ease;
        }
        
        .floating-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 40;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .mobile-menu-open {
                overflow: hidden;
            }
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .dark .modal-content {
            background: #1f2937;
            color: white;
        }
        
        /* Responsive table styles */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(1, 1fr);
            }
            
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-box {
                width: 100%;
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .modal-content {
                padding: 1rem;
                margin: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-900 transition-colors duration-300">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobile-menu-button" class="bg-white dark:bg-gray-800 p-3 rounded-xl shadow-lg hover-lift">
            <i class="fas fa-bars text-gray-700 dark:text-gray-200"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 z-40 w-64 shadow-2xl">
        <!-- Header -->
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chair text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white"><?= htmlspecialchars($settings['site_title']); ?></h1>
                    <p class="text-gray-400 text-sm">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-2">
            <a href="admin-dashboard.php" class="flex items-center space-x-3 p-3 bg-indigo-600 text-white rounded-xl hover-lift">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>
            <button onclick="showTab('students')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-users w-5"></i>
                <span>Students</span>
                <span class="ml-auto bg-indigo-500 text-white text-xs px-2 py-1 rounded-full"><?= $total_students; ?></span>
            </button>
            <button onclick="showTab('rooms')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-door-open w-5"></i>
                <span>Rooms</span>
                <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full"><?= $total_rooms; ?></span>
            </button>
            <button onclick="showTab('departments')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-university w-5"></i>
                <span>Departments</span>
                <span class="ml-auto bg-purple-500 text-white text-xs px-2 py-1 rounded-full"><?= $total_departments; ?></span>
            </button>
            <button onclick="showTab('allocations')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-chair w-5"></i>
                <span>Seat Allocations</span>
                <span class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full"><?= $total_allocations; ?></span>
            </button>
            <button onclick="showTab('notices')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-bullhorn w-5"></i>
                <span>Notices</span>
                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= count($recent_notices); ?></span>
            </button>
            <button onclick="showTab('analytics')" class="flex items-center space-x-3 p-3 text-gray-300 hover:bg-gray-700 rounded-xl w-full text-left hover-lift transition">
                <i class="fas fa-chart-bar w-5"></i>
                <span>Analytics</span>
            </button>
        </nav>

        <!-- User Info -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-shield text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['admin_username']); ?></p>
                    <p class="text-xs text-gray-400">Administrator</p>
                </div>
            </div>
            <a href="?logout=true" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-900 rounded-xl transition">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <!-- Main Content -->
    <div class="main-content lg:ml-64 transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 py-4 px-6">
            <div class="header-content flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="ml-16 lg:ml-0">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                    <p class="text-gray-600 dark:text-gray-400">Welcome back, <?= htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>
                <div class="header-actions flex items-center space-x-4 md:space-x-6">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover-lift">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative search-box">
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full md:w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-500"></i>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden md:block">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Last login</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-white"><?= date('M j, Y g:i A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="m-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="m-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Section -->
        <div class="p-4 md:p-6">
            <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
                <!-- Total Students -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 md:p-6 border-l-4 border-indigo-500 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Students</p>
                            <p class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white"><?= $total_students; ?></p>
                            <div class="flex items-center mt-2">
                                <span class="text-green-500 text-sm flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 12%
                                </span>
                                <span class="text-gray-500 text-sm ml-2 hidden md:inline">from last month</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-indigo-100 dark:bg-indigo-900 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-xl md:text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Rooms -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 md:p-6 border-l-4 border-green-500 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Exam Rooms</p>
                            <p class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white"><?= $total_rooms; ?></p>
                            <div class="flex items-center mt-2">
                                <span class="text-green-500 text-sm flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 5%
                                </span>
                                <span class="text-gray-500 text-sm ml-2 hidden md:inline">from last month</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center">
                            <i class="fas fa-door-open text-green-600 dark:text-green-400 text-xl md:text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Departments -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 md:p-6 border-l-4 border-purple-500 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Departments</p>
                            <p class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white"><?= $total_departments; ?></p>
                            <div class="flex items-center mt-2">
                                <span class="text-gray-500 text-sm">No change</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-purple-100 dark:bg-purple-900 rounded-xl flex items-center justify-center">
                            <i class="fas fa-university text-purple-600 dark:text-purple-400 text-xl md:text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Seat Allocations -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 md:p-6 border-l-4 border-orange-500 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Seat Allocations</p>
                            <p class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white"><?= $total_allocations; ?></p>
                            <div class="flex items-center mt-2">
                                <span class="text-green-500 text-sm flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 24%
                                </span>
                                <span class="text-gray-500 text-sm ml-2 hidden md:inline">from last month</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-orange-100 dark:bg-orange-900 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chair text-orange-600 dark:text-orange-400 text-xl md:text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content active">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Overview</h3>
                            <div class="flex flex-wrap gap-2">
                                <button class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 px-3 py-2 md:px-4 md:py-2 rounded-xl hover:bg-indigo-200 dark:hover:bg-indigo-800 text-sm">
                                    <i class="fas fa-download mr-2"></i>Export
                                </button>
                                <button class="bg-indigo-600 text-white px-3 py-2 md:px-4 md:py-2 rounded-xl hover:bg-indigo-700 text-sm">
                                    <i class="fas fa-plus mr-2"></i>Generate Report
                                </button>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="charts-grid grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <!-- Allocation Chart -->
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 md:p-6 text-white">
                                <h4 class="text-lg font-semibold mb-4">Seat Allocation Status</h4>
                                <div class="h-48 md:h-64">
                                    <canvas id="allocationChart"></canvas>
                                </div>
                            </div>

                            <!-- Department Distribution -->
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg">
                                <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Department Distribution</h4>
                                <div class="h-48 md:h-64">
                                    <canvas id="departmentChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity & Notices -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Recent Activity -->
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg">
                                <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Recent Activity</h4>
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-user-plus text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-800 dark:text-white truncate">New student registered</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">Saykot (Roll: 743738) added to Computer Science</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                        <div class="w-10 h-10 bg-green-100 dark:bg-green-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-chair text-green-600 dark:text-green-400"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-800 dark:text-white truncate">Seat allocated</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">Golam Rabbi assigned to Room A, Seat A-2</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">5 hours ago</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-bullhorn text-purple-600 dark:text-purple-400"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-800 dark:text-white truncate">New notice published</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">Exam Schedule Published for Semester Final</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">1 day ago</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg">
                                <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Quick Stats</h4>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-600 rounded-xl">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Available Rooms</p>
                                            <p class="text-xl font-bold text-gray-800 dark:text-white"><?= $total_rooms; ?></p>
                                        </div>
                                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-door-open text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-600 rounded-xl">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Allocated Seats</p>
                                            <p class="text-xl font-bold text-gray-800 dark:text-white"><?= $total_allocations; ?></p>
                                        </div>
                                        <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chair text-green-600 dark:text-green-400"></i>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-600 rounded-xl">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Actions</p>
                                            <p class="text-xl font-bold text-gray-800 dark:text-white">3</p>
                                        </div>
                                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-800 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-clock text-orange-600 dark:text-orange-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Tab -->
                <div id="students" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Student Management</h3>
                            <button onclick="toggleForm('studentForm')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 flex items-center justify-center w-full md:w-auto">
                                <i class="fas fa-plus mr-2"></i>Add Student
                            </button>
                        </div>

                        <!-- Add Student Form -->
                        <div id="studentForm" class="mb-6 p-4 md:p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl hidden">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Add New Student</h4>
                            <form method="POST">
                                <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                                        <input type="text" name="name" placeholder="Full Name" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Roll Number</label>
                                        <input type="text" name="roll_no" placeholder="Roll Number" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registration Number</label>
                                        <input type="text" name="registration_no" placeholder="Registration Number" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                                        <input type="text" name="department" placeholder="Department" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
                                        <input type="text" name="semester" placeholder="Semester" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Session</label>
                                        <input type="text" name="session" placeholder="Session" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                        <input type="email" name="email" placeholder="Email" class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                        <input type="text" name="phone" placeholder="Phone" class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="submit" name="add_student" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Save Student
                                    </button>
                                    <button type="button" onclick="toggleForm('studentForm')" class="bg-gray-600 text-white px-6 py-3 rounded-xl hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Students Table -->
                        <div class="table-container overflow-x-auto rounded-2xl shadow-lg">
                            <table class="w-full table-auto min-w-max">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Name</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Roll No</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Department</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Semester</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($recent_students as $student): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="fas fa-user text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white truncate"><?= htmlspecialchars($student['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($student['roll_no']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 rounded-full text-xs font-medium truncate">
                                                <?= htmlspecialchars($student['department']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($student['semester']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="editStudent(<?= $student['student_id']; ?>)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewStudent(<?= $student['student_id']; ?>)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmDelete('student', <?= $student['student_id']; ?>)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Rooms Tab -->
                <div id="rooms" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Room Management</h3>
                            <button onclick="toggleForm('roomForm')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 flex items-center justify-center w-full md:w-auto">
                                <i class="fas fa-plus mr-2"></i>Add Room
                            </button>
                        </div>

                        <!-- Add Room Form -->
                        <div id="roomForm" class="mb-6 p-4 md:p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl hidden">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Add New Room</h4>
                            <form method="POST">
                                <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room Name</label>
                                        <input type="text" name="room_name" placeholder="Room Name" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacity</label>
                                        <input type="number" name="capacity" placeholder="Capacity" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Building Name</label>
                                        <input type="text" name="building_name" placeholder="Building Name" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Floor Number</label>
                                        <input type="number" name="floor_number" placeholder="Floor Number" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="submit" name="add_room" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Save Room
                                    </button>
                                    <button type="button" onclick="toggleForm('roomForm')" class="bg-gray-600 text-white px-6 py-3 rounded-xl hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Rooms Table -->
                        <div class="table-container overflow-x-auto rounded-2xl shadow-lg">
                            <table class="w-full table-auto min-w-max">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Room Name</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Building</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Floor</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($rooms as $room): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="fas fa-door-open text-green-600 dark:text-green-400 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white truncate"><?= htmlspecialchars($room['room_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($room['building_name']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($room['floor_number']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 rounded-full text-xs font-medium">
                                                <?= htmlspecialchars($room['capacity']); ?> seats
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="editRoom(<?= $room['room_id']; ?>)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewRoom(<?= $room['room_id']; ?>)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmDelete('room', <?= $room['room_id']; ?>)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Departments Tab -->
                <div id="departments" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Department Management</h3>
                            <button onclick="toggleForm('departmentForm')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 flex items-center justify-center w-full md:w-auto">
                                <i class="fas fa-plus mr-2"></i>Add Department
                            </button>
                        </div>

                        <!-- Add Department Form -->
                        <div id="departmentForm" class="mb-6 p-4 md:p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl hidden">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Add New Department</h4>
                            <form method="POST">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name</label>
                                        <input type="text" name="dept_name" placeholder="Department Name" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Head of Department</label>
                                        <input type="text" name="head_name" placeholder="Head of Department" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
                                        <input type="email" name="contact_email" placeholder="Contact Email" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="submit" name="add_department" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Save Department
                                    </button>
                                    <button type="button" onclick="toggleForm('departmentForm')" class="bg-gray-600 text-white px-6 py-3 rounded-xl hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Departments Table -->
                        <div class="table-container overflow-x-auto rounded-2xl shadow-lg">
                            <table class="w-full table-auto min-w-max">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Department Name</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Head of Department</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Contact Email</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($departments as $dept): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="fas fa-university text-purple-600 dark:text-purple-400 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white truncate"><?= htmlspecialchars($dept['dept_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($dept['head_name']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($dept['contact_email']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="editDepartment(<?= $dept['dept_id']; ?>)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewDepartment(<?= $dept['dept_id']; ?>)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmDelete('department', <?= $dept['dept_id']; ?>)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Allocations Tab -->
                <div id="allocations" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Seat Allocation Management</h3>
                            <button onclick="toggleForm('allocationForm')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 flex items-center justify-center w-full md:w-auto">
                                <i class="fas fa-plus mr-2"></i>Allocate Seat
                            </button>
                        </div>

                        <!-- Allocate Seat Form -->
                        <div id="allocationForm" class="mb-6 p-4 md:p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl hidden">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Allocate Seat to Student</h4>
                            <form method="POST">
                                <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                                        <select name="student_id" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Student</option>
                                            <?php foreach ($all_students as $student): ?>
                                            <option value="<?= $student['student_id']; ?>">
                                                <?= htmlspecialchars($student['name']); ?> (<?= htmlspecialchars($student['roll_no']); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room</label>
                                        <select name="room_id" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Room</option>
                                            <?php foreach ($all_rooms as $room): ?>
                                            <option value="<?= $room['room_id']; ?>">
                                                <?= htmlspecialchars($room['room_name']); ?> (Capacity: <?= $room['capacity']; ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seat Number</label>
                                        <input type="text" name="seat_number" placeholder="Seat Number (e.g., A-1)" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="submit" name="allocate_seat" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Allocate Seat
                                    </button>
                                    <button type="button" onclick="toggleForm('allocationForm')" class="bg-gray-600 text-white px-6 py-3 rounded-xl hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Allocations Table -->
                        <div class="table-container overflow-x-auto rounded-2xl shadow-lg">
                            <table class="w-full table-auto min-w-max">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Student Name</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Roll No</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Room</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Seat Number</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Allocated At</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($allocations as $alloc): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="fas fa-user text-orange-600 dark:text-orange-400 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white truncate"><?= htmlspecialchars($alloc['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($alloc['roll_no']); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 rounded-full text-xs font-medium truncate">
                                                <?= htmlspecialchars($alloc['room_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 rounded-full text-xs font-medium">
                                                <?= htmlspecialchars($alloc['seat_number']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= date('M j, Y', strtotime($alloc['allocated_at'])); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="viewAllocation(<?= $alloc['allocation_id']; ?>)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmDelete('allocation', <?= $alloc['allocation_id']; ?>)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notices Tab -->
                <div id="notices" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Notice Management</h3>
                            <button onclick="toggleForm('noticeForm')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 flex items-center justify-center w-full md:w-auto">
                                <i class="fas fa-plus mr-2"></i>Add Notice
                            </button>
                        </div>

                        <!-- Add Notice Form -->
                        <div id="noticeForm" class="mb-6 p-4 md:p-6 bg-gray-50 dark:bg-gray-700 rounded-2xl hidden">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Add New Notice</h4>
                            <form method="POST">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notice Title</label>
                                        <input type="text" name="title" placeholder="Notice Title" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <textarea name="description" placeholder="Notice Description" required rows="4" class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                    </div>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button type="submit" name="add_notice" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Publish Notice
                                    </button>
                                    <button type="button" onclick="toggleForm('noticeForm')" class="bg-gray-600 text-white px-6 py-3 rounded-xl hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Notices Table -->
                        <div class="table-container overflow-x-auto rounded-2xl shadow-lg">
                            <table class="w-full table-auto min-w-max">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Title</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Description</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Posted At</th>
                                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($recent_notices as $notice): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                    <i class="fas fa-bullhorn text-red-600 dark:text-red-400 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white truncate"><?= htmlspecialchars($notice['title']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400 truncate max-w-xs"><?= htmlspecialchars(substr($notice['description'], 0, 100)); ?>...</td>
                                        <td class="px-4 py-3 md:px-6 md:py-4 text-gray-600 dark:text-gray-400"><?= date('M j, Y', strtotime($notice['posted_at'])); ?></td>
                                        <td class="px-4 py-3 md:px-6 md:py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="editNotice(<?= $notice['notice_id']; ?>)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewNotice(<?= $notice['notice_id']; ?>)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmDelete('notice', <?= $notice['notice_id']; ?>)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div id="analytics" class="tab-content">
                    <div class="p-4 md:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">System Analytics</h3>
                            <div class="flex space-x-2">
                                <select class="bg-gray-100 dark:bg-gray-700 border-0 rounded-xl px-4 py-2 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    <option>Last 7 days</option>
                                    <option>Last 30 days</option>
                                    <option>Last 90 days</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                            <!-- Performance Metrics -->
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 md:p-6 text-white">
                                <h4 class="text-lg font-semibold mb-4">System Performance</h4>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span>Server Uptime</span>
                                        <span class="font-bold">99.8%</span>
                                    </div>
                                    <div class="w-full bg-indigo-400 rounded-full h-2">
                                        <div class="bg-white h-2 rounded-full" style="width: 99.8%"></div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center mt-6">
                                        <span>Database Performance</span>
                                        <span class="font-bold">98.5%</span>
                                    </div>
                                    <div class="w-full bg-indigo-400 rounded-full h-2">
                                        <div class="bg-white h-2 rounded-full" style="width: 98.5%"></div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center mt-6">
                                        <span>Response Time</span>
                                        <span class="font-bold">0.8s</span>
                                    </div>
                                    <div class="w-full bg-indigo-400 rounded-full h-2">
                                        <div class="bg-white h-2 rounded-full" style="width: 95%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- User Activity -->
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg col-span-1 lg:col-span-2">
                                <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">User Activity Timeline</h4>
                                <div class="h-48 md:h-64">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Analytics -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg text-center">
                                <div class="w-12 h-12 md:w-16 md:h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl md:text-2xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 dark:text-white text-sm md:text-base">Successful Operations</h5>
                                <p class="text-xl md:text-3xl font-bold text-gray-800 dark:text-white mt-2">98.2%</p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg text-center">
                                <div class="w-12 h-12 md:w-16 md:h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl md:text-2xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 dark:text-white text-sm md:text-base">Active Users</h5>
                                <p class="text-xl md:text-3xl font-bold text-gray-800 dark:text-white mt-2">142</p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg text-center">
                                <div class="w-12 h-12 md:w-16 md:h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-database text-orange-600 dark:text-orange-400 text-xl md:text-2xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 dark:text-white text-sm md:text-base">Data Storage</h5>
                                <p class="text-xl md:text-3xl font-bold text-gray-800 dark:text-white mt-2">2.4GB</p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg text-center">
                                <div class="w-12 h-12 md:w-16 md:h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-shield-alt text-purple-600 dark:text-purple-400 text-xl md:text-2xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 dark:text-white text-sm md:text-base">Security Score</h5>
                                <p class="text-xl md:text-3xl font-bold text-gray-800 dark:text-white mt-2">96.7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Confirm Deletion</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6" id="deleteMessage">Are you sure you want to delete this item?</p>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 justify-center">
                    <button id="confirmDelete" class="bg-red-600 text-white px-6 py-2 rounded-xl hover:bg-red-700">Delete</button>
                    <button onclick="closeModal('deleteModal')" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white" id="viewTitle">Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="viewContent" class="text-gray-600 dark:text-gray-400">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white" id="editTitle">Edit</h3>
                <button onclick="closeModal('editModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="editContent">
                <!-- Edit form will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.querySelector('.sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');
        const mainContent = document.querySelector('.main-content');

        function openMobileMenu() {
            sidebar.classList.add('open');
            mobileOverlay.classList.remove('hidden');
            document.body.classList.add('mobile-menu-open');

            // Hide menu button
            mobileMenuButton.classList.add('hidden');
        }

        function closeMobileMenu() {
            sidebar.classList.remove('open');
            mobileOverlay.classList.add('hidden');
            document.body.classList.remove('mobile-menu-open');

            // Show menu button again
            mobileMenuButton.classList.remove('hidden');
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        mobileOverlay.addEventListener('click', closeMobileMenu);

        

        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Close mobile menu on tab change (for mobile)
            closeMobileMenu();
        }

        // Form toggle functionality
        function toggleForm(formId) {
            const form = document.getElementById(formId);
            form.classList.toggle('hidden');
        }

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        // Delete confirmation
        function confirmDelete(type, id) {
            const modal = document.getElementById('deleteModal');
            const message = document.getElementById('deleteMessage');
            const confirmBtn = document.getElementById('confirmDelete');
            
            let deleteUrl = '';
            switch(type) {
                case 'student':
                    message.textContent = 'Are you sure you want to delete this student? This action cannot be undone.';
                    deleteUrl = `?delete_student=${id}`;
                    break;
                case 'room':
                    message.textContent = 'Are you sure you want to delete this room? This action cannot be undone.';
                    deleteUrl = `?delete_room=${id}`;
                    break;
                case 'department':
                    message.textContent = 'Are you sure you want to delete this department? This action cannot be undone.';
                    deleteUrl = `?delete_department=${id}`;
                    break;
                case 'notice':
                    message.textContent = 'Are you sure you want to delete this notice? This action cannot be undone.';
                    deleteUrl = `?delete_notice=${id}`;
                    break;
                case 'allocation':
                    message.textContent = 'Are you sure you want to delete this seat allocation? This action cannot be undone.';
                    deleteUrl = `?delete_allocation=${id}`;
                    break;
            }
            
            confirmBtn.onclick = function() {
                window.location.href = deleteUrl;
            };
            
            openModal('deleteModal');
        }

        // View functions (placeholder implementations)
        function viewStudent(id) {
            // In a real application, you would fetch student data via AJAX
            document.getElementById('viewTitle').textContent = 'Student Details';
            document.getElementById('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium">Name:</span>
                        <span>Student Name</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Roll No:</span>
                        <span>743738</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Department:</span>
                        <span>Computer Science</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Semester:</span>
                        <span>6th</span>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        function viewRoom(id) {
            document.getElementById('viewTitle').textContent = 'Room Details';
            document.getElementById('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium">Room Name:</span>
                        <span>Room A</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Building:</span>
                        <span>Main Building</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Floor:</span>
                        <span>1</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Capacity:</span>
                        <span>50 seats</span>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        function viewDepartment(id) {
            document.getElementById('viewTitle').textContent = 'Department Details';
            document.getElementById('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium">Department Name:</span>
                        <span>Computer Science</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Head of Department:</span>
                        <span>Dr. A. Rahman</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Contact Email:</span>
                        <span>cs@college.com</span>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        function viewNotice(id) {
            document.getElementById('viewTitle').textContent = 'Notice Details';
            document.getElementById('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium">Title:</span>
                        <span>Exam Schedule Published</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Description:</span>
                        <span class="text-sm">The semester final exam schedule has been published. Please check the notice board.</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Posted At:</span>
                        <span>Oct 30, 2025</span>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        function viewAllocation(id) {
            document.getElementById('viewTitle').textContent = 'Seat Allocation Details';
            document.getElementById('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-medium">Student:</span>
                        <span>Saykot (743738)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Room:</span>
                        <span>Room A (Main Building)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Seat Number:</span>
                        <span>A-1</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Allocated At:</span>
                        <span>Oct 30, 2025</span>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        // Edit functions (placeholder implementations)
        function editStudent(id) {
            document.getElementById('editTitle').textContent = 'Edit Student';
            document.getElementById('editContent').innerHTML = `
                <form method="POST">
                    <input type="hidden" name="student_id" value="${id}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" name="name" value="Saykot" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Roll Number</label>
                            <input type="text" name="roll_no" value="743738" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <button type="submit" name="edit_student" class="bg-green-600 text-white px-6 py-2 rounded-xl hover:bg-green-700">Update</button>
                            <button type="button" onclick="closeModal('editModal')" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </form>
            `;
            openModal('editModal');
        }

        function editRoom(id) {
            document.getElementById('editTitle').textContent = 'Edit Room';
            document.getElementById('editContent').innerHTML = `
                <form method="POST">
                    <input type="hidden" name="room_id" value="${id}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room Name</label>
                            <input type="text" name="room_name" value="Room A" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacity</label>
                            <input type="number" name="capacity" value="50" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <button type="submit" name="edit_room" class="bg-green-600 text-white px-6 py-2 rounded-xl hover:bg-green-700">Update</button>
                            <button type="button" onclick="closeModal('editModal')" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </form>
            `;
            openModal('editModal');
        }

        function editDepartment(id) {
            document.getElementById('editTitle').textContent = 'Edit Department';
            document.getElementById('editContent').innerHTML = `
                <form method="POST">
                    <input type="hidden" name="dept_id" value="${id}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name</label>
                            <input type="text" name="dept_name" value="Computer Science" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Head of Department</label>
                            <input type="text" name="head_name" value="Dr. A. Rahman" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <button type="submit" name="edit_department" class="bg-green-600 text-white px-6 py-2 rounded-xl hover:bg-green-700">Update</button>
                            <button type="button" onclick="closeModal('editModal')" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </form>
            `;
            openModal('editModal');
        }

        function editNotice(id) {
            document.getElementById('editTitle').textContent = 'Edit Notice';
            document.getElementById('editContent').innerHTML = `
                <form method="POST">
                    <input type="hidden" name="notice_id" value="${id}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input type="text" name="title" value="Exam Schedule Published" required class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea name="description" required rows="4" class="w-full p-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl">The semester final exam schedule has been published. Please check the notice board.</textarea>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <button type="submit" name="edit_notice" class="bg-green-600 text-white px-6 py-2 rounded-xl hover:bg-green-700">Update</button>
                            <button type="button" onclick="closeModal('editModal')" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </form>
            `;
            openModal('editModal');
        }

        // Close menu when clicking on a link
        document.querySelectorAll('.sidebar a, .sidebar button').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileMenu();
            }
        });

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(msg => {
                if (msg) {
                    msg.style.display = 'none';
                }
            });
        }, 5000);

        // Chart instances
        let allocationChart, departmentChart, activityChart;

        // Initialize Charts
        function initCharts() {
            // Allocation Chart
            const allocationCtx = document.getElementById('allocationChart');
            if (allocationCtx) {
                allocationChart = new Chart(allocationCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Allocated', 'Available'],
                        datasets: [{
                            data: [<?= $total_allocations; ?>, <?= $total_students - $total_allocations; ?>],
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(255, 255, 255, 0.3)'
                            ],
                            borderColor: [
                                'rgba(99, 102, 241, 1)',
                                'rgba(255, 255, 255, 0.5)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: 'white',
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Department Chart
            const departmentCtx = document.getElementById('departmentChart');
            if (departmentCtx) {
                const isDark = document.documentElement.classList.contains('dark');
                departmentChart = new Chart(departmentCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Computer Science', 'Electrical', 'Civil'],
                        datasets: [{
                            label: 'Students',
                            data: [120, 80, 60],
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.7)',
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(245, 158, 11, 0.7)'
                            ],
                            borderColor: [
                                'rgba(99, 102, 241, 1)',
                                'rgba(16, 185, 129, 1)',
                                'rgba(245, 158, 11, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: isDark ? '#9CA3AF' : '#6B7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: isDark ? '#9CA3AF' : '#6B7280'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Activity Chart
            const activityCtx = document.getElementById('activityChart');
            if (activityCtx) {
                const isDark = document.documentElement.classList.contains('dark');
                activityChart = new Chart(activityCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'User Logins',
                            data: [65, 78, 66, 72, 80, 55, 40],
                            borderColor: 'rgba(99, 102, 241, 1)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: isDark ? '#9CA3AF' : '#6B7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: isDark ? '#9CA3AF' : '#6B7280'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: isDark ? '#9CA3AF' : '#6B7280'
                                }
                            }
                        }
                    }
                });
            }
        }

        // Refresh charts when theme changes
        function refreshCharts() {
            if (allocationChart) allocationChart.destroy();
            if (departmentChart) departmentChart.destroy();
            if (activityChart) activityChart.destroy();
            
            initCharts();
        }

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>

<?php $conn->close(); ?>