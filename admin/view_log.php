<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Path to the log file
$log_path = '../website_generation.log';
$log_content = '';

if (file_exists($log_path)) {
    $log_content = file_get_contents($log_path);
} else {
    $log_content = 'No log file found.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Generation Log - Wedding Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Website Generation Log</h1>
                    <div class="flex items-center space-x-4">
                        <a href="edit_content.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Editor
                        </a>
                        <a href="#" onclick="clearLog(); return false;" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Clear Log
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow container mx-auto py-8 px-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Log Contents</h2>
                <pre class="bg-gray-100 p-4 rounded-lg overflow-auto h-[70vh] whitespace-pre-wrap"><?php echo htmlspecialchars($log_content); ?></pre>
            </div>
        </div>
    </div>

    <script>
        function clearLog() {
            if (confirm('Are you sure you want to clear the log?')) {
                fetch('clear_log.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error clearing log: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
            }
        }
    </script>
</body>
</html> 