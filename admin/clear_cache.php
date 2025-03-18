<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Delete cached files function
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

// Clear any server-side caches (example for popular cache directories)
$cacheDirectories = [
    '../cache/',
    '../tmp/cache/', 
    '../var/cache/'
];

foreach ($cacheDirectories as $dir) {
    if (file_exists($dir)) {
        deleteDirectory($dir);
    }
}

// Force reload of CSS and JS by appending a version parameter
$version = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache - Amoureturnel Admin</title>
    <script src="https://cdn.tailwindcss.com?v=<?php echo $version; ?>"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
            min-height: 100vh;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .spin-animation {
            animation: spin 2s linear infinite;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4">
        <div class="text-center mb-8">
            <div class="h-20 w-20 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg mx-auto mb-4">
                <i class="fas fa-sync-alt text-white text-3xl spin-animation"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Cache Cleared</h1>
            <p class="text-gray-600 max-w-md">All browser and server caches have been cleared. You should now see the updated UI across the admin dashboard.</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 max-w-md w-full">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Next Steps</h2>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mt-0.5">
                        <i class="fas fa-check text-green-600 text-xs"></i>
                    </div>
                    <p class="ml-3 text-gray-600">Server cache cleared</p>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mt-0.5">
                        <i class="fas fa-check text-green-600 text-xs"></i>
                    </div>
                    <p class="ml-3 text-gray-600">Local browser cache cleared</p>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-yellow-100 flex items-center justify-center mt-0.5">
                        <i class="fas fa-info text-yellow-600 text-xs"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-gray-600">If you still see old UI, try:</p>
                        <ul class="list-disc ml-5 mt-1 text-sm text-gray-500">
                            <li>Hard refresh (Ctrl+F5 or Cmd+Shift+R)</li>
                            <li>Try a different browser</li>
                            <li>Clear browser cache manually</li>
                            <li>Try an incognito/private window</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-between space-x-4">
                <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                </a>
                <a href="edit_content.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <i class="fas fa-edit mr-2"></i> Edit Content
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Clear browser cache
        if (window.caches) {
            caches.keys().then(function(names) {
                for (let name of names)
                    caches.delete(name);
            });
        }
        
        // Clear localStorage
        if (window.localStorage) {
            localStorage.clear();
        }
        
        // Clear sessionStorage
        if (window.sessionStorage) {
            sessionStorage.clear();
        }
        
        // Force reload of CSS and JS files when going to other pages
        document.querySelectorAll('a').forEach(link => {
            if (!link.href.includes('?v=')) {
                link.href = link.href + (link.href.includes('?') ? '&' : '?') + 'v=<?php echo $version; ?>';
            }
        });
    </script>
</body>
</html> 