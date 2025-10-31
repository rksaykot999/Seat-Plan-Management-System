<?php
session_start();
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

// Initialize variables
$error = '';
$success = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_no = $conn->real_escape_string($_POST['roll_no']);
    $registration_no = $conn->real_escape_string($_POST['registration_no']);
    
    // Basic validation
    if (empty($roll_no) || empty($registration_no)) {
        $error = "Please enter both roll number and registration number.";
    } else {
        // Check student credentials
        $sql = "SELECT s.*, sa.seat_number, sa.room_id, r.room_name, r.building_name 
                FROM students s 
                LEFT JOIN seat_allocations sa ON s.student_id = sa.student_id 
                LEFT JOIN rooms r ON sa.room_id = r.room_id 
                WHERE s.roll_no = '$roll_no' AND s.registration_no = '$registration_no'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $student = $result->fetch_assoc();
            
            // Set session variables
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['student_roll_no'] = $student['roll_no'];
            $_SESSION['student_registration_no'] = $student['registration_no'];
            $_SESSION['student_department'] = $student['department'];
            $_SESSION['student_semester'] = $student['semester'];
            $_SESSION['student_session'] = $student['session'];
            $_SESSION['student_email'] = $student['email'];
            $_SESSION['student_phone'] = $student['phone'];
            $_SESSION['student_seat_number'] = $student['seat_number'];
            $_SESSION['student_room_name'] = $student['room_name'];
            $_SESSION['student_building_name'] = $student['building_name'];
            $_SESSION['student_logged_in'] = true;
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid roll number or registration number. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - <?= htmlspecialchars($settings['site_title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/906/906175.png">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
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
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <!-- Background Pattern -->
    <div class="absolute inset-0 gradient-bg"></div>
    <div class="absolute inset-0 bg-black/10"></div>
    
    <!-- Login Container -->
    <div class="relative w-full max-w-md mx-4">
        <div class="login-container rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-user-graduate text-3xl gradient-text"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Student Login</h1>
                <p class="text-white/80">Access your exam seat information</p>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="space-y-6">
                <div>
                    <label for="roll_no" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-id-card mr-2"></i>Roll Number
                    </label>
                    <input 
                        type="text" 
                        id="roll_no" 
                        name="roll_no" 
                        required 
                        class="w-full px-4 py-3 bg-white/90 border border-gray-300 rounded-lg form-input focus:ring-2 focus:ring-white focus:border-white transition placeholder-gray-500"
                        placeholder="Enter your roll number"
                        value="<?= isset($_POST['roll_no']) ? htmlspecialchars($_POST['roll_no']) : '' ?>"
                    >
                </div>
                
                <div>
                    <label for="registration_no" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-file-alt mr-2"></i>Registration Number
                    </label>
                    <input 
                        type="text" 
                        id="registration_no" 
                        name="registration_no" 
                        required 
                        class="w-full px-4 py-3 bg-white/90 border border-gray-300 rounded-lg form-input focus:ring-2 focus:ring-white focus:border-white transition placeholder-gray-500"
                        placeholder="Enter your registration number"
                        value="<?= isset($_POST['registration_no']) ? htmlspecialchars($_POST['registration_no']) : '' ?>"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition duration-300 flex items-center justify-center shadow-lg transform hover:scale-105"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Dashboard
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 p-4 bg-white/20 rounded-lg">
                <h3 class="text-white font-semibold mb-2 text-sm">Demo Credentials:</h3>
                <div class="text-white/80 text-xs space-y-1">
                    <p><strong>Roll No:</strong> 743738</p>
                    <p><strong>Registration No:</strong> 1502269452</p>
                    <p class="text-white/60 text-xs mt-2">Or check the database for other students</p>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a 
                    href="index.php" 
                    class="text-white hover:text-white/80 transition flex items-center justify-center text-sm"
                >
                    <i class="fas fa-arrow-left mr-2"></i> Back to Homepage
                </a>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center mt-6">
            <p class="text-white/60 text-sm">
                &copy; 2025 <?= htmlspecialchars($settings['site_title']); ?>. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute bottom-10 left-10">
        <div class="bg-white/20 backdrop-blur-sm rounded-full p-4 floating">
            <i class="fas fa-graduation-cap text-white text-xl"></i>
        </div>
    </div>
    <div class="absolute top-10 right-10">
        <div class="bg-white/20 backdrop-blur-sm rounded-full p-4 floating" style="animation-delay: 0.5s;">
            <i class="fas fa-book text-white text-xl"></i>
        </div>
    </div>

    <script>
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const rollNo = document.getElementById('roll_no');
            const regNo = document.getElementById('registration_no');
            
            if (!rollNo.value.trim() || !regNo.value.trim()) {
                e.preventDefault();
                if (!rollNo.value.trim()) {
                    rollNo.classList.add('border-red-500');
                }
                if (!regNo.value.trim()) {
                    regNo.classList.add('border-red-500');
                }
            }
        });

        // Remove error styles on input
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });
    </script>
</body>
</html>