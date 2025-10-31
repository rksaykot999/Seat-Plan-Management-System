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
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Check admin credentials
        $sql = "SELECT * FROM admins WHERE username = '$username'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password (plain text comparison as per your database)
            if ($password === $admin['password']) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_logged_in'] = true;
                
                // Redirect to dashboard
                header("Location: admin-dashboard.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "Admin username not found.";
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
    <title>Admin Login - <?= htmlspecialchars($settings['site_title']); ?></title>
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
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .form-input {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            border-color: var(--primary);
            background: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); }
            100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
        }
        
        /* Background Animation */
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .bg-animation div {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: animate 25s linear infinite;
            bottom: -150px;
        }
        
        .bg-animation div:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }
        
        .bg-animation div:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }
        
        .bg-animation div:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }
        
        .bg-animation div:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }
        
        .bg-animation div:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
        }
        
        .bg-animation div:nth-child(6) {
            left: 75%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }
        
        .bg-animation div:nth-child(7) {
            left: 35%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }
        
        .bg-animation div:nth-child(8) {
            left: 50%;
            width: 25px;
            height: 25px;
            animation-delay: 15s;
            animation-duration: 45s;
        }
        
        .bg-animation div:nth-child(9) {
            left: 20%;
            width: 15px;
            height: 15px;
            animation-delay: 2s;
            animation-duration: 35s;
        }
        
        .bg-animation div:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-delay: 0s;
            animation-duration: 11s;
        }
        
        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .glass-card {
                backdrop-filter: blur(10px);
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <!-- Animated Background -->
    <div class="bg-animation">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    
    <!-- Main Background -->
    <div class="absolute inset-0 gradient-bg"></div>
    
    <!-- Login Container -->
    <div class="relative w-full max-w-md">     
        <!-- Main Login Card -->
        <div class="glass-card rounded-3xl shadow-2xl overflow-hidden" data-aos="zoom-in">
            <!-- Header Section -->
            <div class="gradient-bg p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full opacity-10">
                    <div class="absolute top-4 left-4 w-8 h-8 bg-white rounded-full"></div>
                    <div class="absolute top-12 right-8 w-6 h-6 bg-white rounded-full"></div>
                    <div class="absolute bottom-8 left-10 w-10 h-10 bg-white rounded-full"></div>
                </div>
                
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg pulse">
                        <i class="fas fa-user-shield text-3xl gradient-text" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Admin Portal</h1>
                    <p class="text-white/80">Secure Access to Dashboard</p>
                </div>
            </div>

            <!-- Form Section -->
            <div class="p-8">
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 animate-pulse" data-aos="fade-down">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-red-700 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6" data-aos="fade-down">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-green-700 text-sm font-medium"><?= htmlspecialchars($success) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-2">
                                <i class="fas fa-user text-indigo-600 text-sm"></i>
                            </div>
                            Username
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl form-input focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-500"
                                placeholder="Enter your username"
                                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                            >
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-2">
                                <i class="fas fa-lock text-indigo-600 text-sm"></i>
                            </div>
                            Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl form-input focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-500"
                                placeholder="Enter your password"
                            >
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <button type="button" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-gray-600 text-sm cursor-pointer">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                            Remember me
                        </label>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition">
                            Forgot password?
                        </a>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full btn-primary text-white px-6 py-4 rounded-xl font-semibold transition duration-300 flex items-center justify-center shadow-lg"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Dashboard
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 rounded-xl">
                    <h3 class="text-indigo-800 font-semibold mb-3 text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Demo Credentials
                    </h3>
                    <div class="text-gray-700 text-xs space-y-2">
                        <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                            <span class="font-medium">Username:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">admin</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                            <span class="font-medium">Password:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">1234</span>
                        </div>
                    </div>
                </div>

                <!-- Back to Home -->
                <div class="text-center mt-6 pt-6 border-t border-gray-200">
                    <a 
                        href="index.php" 
                        class="text-gray-600 hover:text-indigo-600 transition flex items-center justify-center text-sm font-medium"
                    >
                        <i class="fas fa-arrow-left mr-2"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center mt-8">
            <p class="text-white/80 text-sm">
                &copy; 2025 <?= htmlspecialchars($settings['site_title']); ?>. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Form validation
        const form = document.querySelector('form');
        const inputs = document.querySelectorAll('input[required]');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500', 'bg-red-50');
                    
                    // Add error message if not exists
                    if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message text-red-500 text-xs mt-1';
                        errorMsg.textContent = 'This field is required';
                        input.parentNode.appendChild(errorMsg);
                    }
                } else {
                    input.classList.remove('border-red-500', 'bg-red-50');
                    
                    // Remove error message if exists
                    const errorMsg = input.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Shake animation for invalid fields
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('animate-pulse');
                        setTimeout(() => {
                            input.classList.remove('animate-pulse');
                        }, 1000);
                    }
                });
            }
        });

        // Remove error styles on input
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500', 'bg-red-50');
                
                const errorMsg = this.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });

        // Add focus effects with animation
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-indigo-200', 'rounded-xl');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-indigo-200', 'rounded-xl');
            });
        });

        // Add loading state to submit button
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Authenticating...';
            submitBtn.disabled = true;
            
            // Revert after 3 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    </script>
</body>
</html>