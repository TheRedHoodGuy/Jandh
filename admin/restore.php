<?php
// No session check - this is an emergency restore tool that should always work
// even if the session is broken or user can't login

// Get the paths
$template_path = '../index.html';
$backup_path = '../index.html.original';

// Message to display
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'restore') {
        // Check if backup exists
        if (file_exists($backup_path)) {
            // Make a backup of the current broken file just in case
            $broken_backup = '../index.html.broken.' . time();
            copy($template_path, $broken_backup);
            
            // Restore from backup
            if (copy($backup_path, $template_path)) {
                $message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    <strong>Success!</strong> Your website has been restored from the original backup.
                    A backup of the broken version was saved as ' . basename($broken_backup) . '.
                </div>';
            } else {
                $message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    <strong>Error!</strong> Failed to restore from backup. Check file permissions.
                </div>';
            }
        } else {
            $message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                <strong>Error!</strong> No backup file found at ' . $backup_path . '.
            </div>';
        }
    }
}

// Check if backup file exists
$backup_exists = file_exists($backup_path);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Website Restoration</title>
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
            min-height: 100vh;
        }
        
        .restore-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.8);
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
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <div class="flex items-center justify-center mb-6">
                <div class="h-16 w-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-tools text-white text-3xl"></i>
                </div>
                <span class="ml-4 text-4xl font-extrabold text-gray-900">Site Recovery</span>
            </div>
            
            <div class="restore-card rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-wrench mr-3"></i> Wedding Website Restoration
                    </h1>
                    <p class="mt-2 opacity-80">Fix your website quickly and easily</p>
                </div>
                
                <div class="p-8">
                    <?php if (!empty($message)): ?>
                        <?php echo $message; ?>
                    <?php endif; ?>
                    
                    <div class="bg-indigo-50 p-5 rounded-xl mb-6 border border-indigo-100">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">
                            <i class="fas fa-info-circle text-indigo-500 mr-2"></i> About This Tool
                        </h2>
                        <p class="text-gray-600 mb-2">
                            This emergency recovery tool restores your website to its original working state from the backup.
                            Use this if your site is broken, displaying incorrectly, or you need to start fresh.
                        </p>
                        <p class="text-gray-600">
                            <strong>Note:</strong> This will reset your website HTML to the original template, but 
                            your database content will remain unchanged.
                        </p>
                    </div>
                    
                    <div class="mb-8 border-b border-gray-200 pb-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Restore Options</h3>
                        
                        <?php if ($backup_exists): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to restore the website from backup? This will overwrite your current website.')">
                                <input type="hidden" name="action" value="restore">
                                <button type="submit" class="w-full py-4 btn-gradient text-white rounded-xl transition-colors flex items-center justify-center text-lg font-medium">
                                    <i class="fas fa-undo-alt mr-2"></i> Restore Website from Original Backup
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                                <strong>Warning!</strong> No backup file was found. Please contact your administrator.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex justify-center space-x-6">
                        <a href="../index.html" target="_blank" class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-globe mr-2"></i> View Website
                        </a>
                        <a href="simple_edit.php" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit Website
                        </a>
                        <a href="index.php" class="px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 