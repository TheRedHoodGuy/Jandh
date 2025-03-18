<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amoureturnel | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f9fafb;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(232, 121, 249, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(96, 165, 250, 0.1) 0px, transparent 50%);
            background-size: 100% 100%;
            background-repeat: no-repeat;
            height: 100vh;
        }
        
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .btn-gradient {
            background: linear-gradient(to right, #4F46E5, #8B5CF6);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(to right, #4338CA, #7C3AED);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.5);
            transform: translateY(-2px);
        }
        
        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-float {
            animation: floatIn 0.5s ease-out forwards;
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
        <div class="animate-float opacity-0" style="animation-delay: 0.1s">
            <div class="flex items-center justify-center mb-6">
                <div class="h-16 w-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-heart text-white text-3xl"></i>
                </div>
                <span class="ml-4 text-4xl font-extrabold text-gray-900">Amoureturnel</span>
            </div>
        </div>
        
        <div class="animate-float opacity-0 w-full max-w-md" style="animation-delay: 0.2s">
            <div class="login-card rounded-2xl shadow-xl overflow-hidden">
                <div class="px-10 py-12">
                    <h2 class="text-center text-3xl font-bold text-gray-900 mb-8">
                        Admin Login
                    </h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="animate-float opacity-0 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" style="animation-delay: 0.3s">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm"><?php echo $error; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="username" name="username" type="text" required 
                                    class="input-field pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="Enter your username">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" required 
                                    class="input-field pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="Enter your password">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                class="btn-gradient w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sign-in-alt mr-2"></i> Sign in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-6 text-sm text-gray-500">
                <p>Protected access for wedding administrators only.</p>
                <p class="mt-1">Return to <a href="../index.html" class="text-indigo-600 hover:text-indigo-800">wedding website</a>.</p>
            </div>
        </div>
    </div>
</body>
</html> 