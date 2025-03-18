<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get content from database
function getContent($section = null) {
    global $pdo;
    
    $query = "SELECT * FROM website_content";
    if ($section) {
        $query .= " WHERE section = :section";
    }
    $query .= " ORDER BY section, field_name";
    
    $stmt = $pdo->prepare($query);
    if ($section) {
        $stmt->bindParam(':section', $section);
    }
    $stmt->execute();
    
    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['section']][$row['field_name']] = [
            'value' => $row['field_value'],
            'type' => $row['field_type']
        ];
    }
    
    return $result;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Loop through each section
    foreach ($_POST as $key => $value) {
        if (strpos($key, '_') !== false) {
            list($section, $field) = explode('_', $key, 2);
            
            // Update the database
            $stmt = $pdo->prepare("UPDATE website_content SET field_value = ? WHERE section = ? AND field_name = ?");
            $stmt->execute([$value, $section, $field]);
        }
    }
    
    // Handle file uploads
    if (!empty($_FILES)) {
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Extract section and field from key
                if (strpos($key, '_') !== false) {
                    list($section, $field) = explode('_', $key, 2);
                    
                    // Define upload directory
                    $upload_dir = '../assets/img/uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $filename = uniqid() . '_' . basename($file['name']);
                    $filepath = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Get relative path for storage
                        $relative_path = 'assets/img/uploads/' . $filename;
                        
                        // Update database with the file path
                        $stmt = $pdo->prepare("UPDATE website_content SET field_value = ? WHERE section = ? AND field_name = ?");
                        $stmt->execute([$relative_path, $section, $field]);
                    }
                }
            }
        }
    }
    
    // Apply changes to the template
    updateTemplate();
    
    // Redirect to prevent form resubmission
    header('Location: edit_content.php?updated=1');
    exit;
}

// Get all content
$content = getContent();

// Helper function to render input field based on type
function renderInputField($section, $field, $data) {
    $id = $section . '_' . $field;
    $name = $id;
    $value = htmlspecialchars($data['value']);
    $type = $data['type'];
    
    $html = '';
    
    switch ($type) {
        case 'textarea':
            $html = "<textarea id=\"$id\" name=\"$name\" class=\"w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200\" rows=\"4\">$value</textarea>";
            break;
            
        case 'datetime':
            $html = "<input type=\"datetime-local\" id=\"$id\" name=\"$name\" value=\"$value\" class=\"w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200\">";
            break;
            
        case 'image':
            $html = "
            <div class='space-y-2'>
                <input type=\"text\" id=\"$id\" name=\"$name\" value=\"$value\" class=\"w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200\" placeholder=\"Enter image URL\">
                <div class=\"h-24 w-full rounded-xl bg-cover bg-center\" style=\"background-image: url('$value');\"></div>
                <p class=\"text-xs text-gray-500\">Enter a full URL (starting with http:// or https://)</p>
            </div>";
            break;
            
        default: // text
            $html = "<input type=\"text\" id=\"$id\" name=\"$name\" value=\"$value\" class=\"w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200\">";
    }
    
    return $html;
}

// Humanize field name
function humanizeFieldName($fieldName) {
    return ucwords(str_replace('_', ' ', $fieldName));
}

// Sections with their icons and colors
$sectionIcons = [
    'hero' => ['icon' => 'fa-image', 'color' => 'indigo'],
    'date' => ['icon' => 'fa-calendar-days', 'color' => 'blue'],
    'story' => ['icon' => 'fa-book-open', 'color' => 'pink'],
    'events' => ['icon' => 'fa-champagne-glasses', 'color' => 'purple'],
    'gallery' => ['icon' => 'fa-images', 'color' => 'green'],
    'map' => ['icon' => 'fa-map-location-dot', 'color' => 'red'],
    'rsvp' => ['icon' => 'fa-envelope-open-text', 'color' => 'yellow'],
    'footer' => ['icon' => 'fa-shoe-prints', 'color' => 'gray']
];

/**
 * Updates the template with content from the database
 */
function updateTemplate() {
    global $pdo;
    
    // Get template file
    $template_path = '../index.html';
    $template_content = file_get_contents($template_path);
    
    // Get all content from database
    $stmt = $pdo->query("SELECT * FROM website_content");
    $content_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Replace placeholders with content
    foreach ($content_items as $item) {
        $placeholder = '{{' . $item['section'] . '_' . $item['field_name'] . '}}';
        $template_content = str_replace($placeholder, $item['field_value'], $template_content);
    }
    
    // Write updated content back to the file
    file_put_contents($template_path, $template_content);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content - Amoureturnel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --secondary: #8B5CF6;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
            --light: #F3F4F6;
            --dark: #1F2937;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out forwards;
        }
        
        /* Card hover effects */
        .section-card {
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Section tab styles */
        .section-tab {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .section-tab.active {
            background-color: #f3f4f6;
            border-left-color: var(--primary);
        }
        
        .section-tab:hover:not(.active) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col h-0 flex-1 bg-gradient-to-b from-indigo-700 to-purple-700 rounded-r-3xl shadow-xl">
                    <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <div class="flex items-center justify-center flex-shrink-0 px-4">
                            <div class="h-12 w-12 bg-white rounded-xl flex items-center justify-center">
                                <i class="fas fa-heart text-indigo-600 text-2xl"></i>
                            </div>
                            <span class="ml-3 text-white text-xl font-extrabold">Amoureturnel</span>
                        </div>
                        <nav class="mt-10 flex-1 px-4 space-y-1">
                            <a href="index.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-gauge-high mr-3 text-lg"></i>
                                Dashboard
                            </a>
                            <a href="edit_content.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl bg-white bg-opacity-20 text-white">
                                <i class="fas fa-edit mr-3 text-lg"></i>
                                Edit Content
                            </a>
                            <a href="rsvp.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-envelope mr-3 text-lg"></i>
                                RSVP Management
                            </a>
                            <a href="../index.html" target="_blank" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-globe mr-3 text-lg"></i>
                                View Website
                            </a>
                        </nav>
                    </div>
                    <div class="p-4">
                        <a href="logout.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                            <i class="fas fa-sign-out-alt mr-3 text-lg"></i>
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <!-- Top navbar -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow-sm">
                <button type="button" class="md:hidden px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars"></i>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h1 class="text-2xl font-semibold text-gray-800">Edit Website Content</h1>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <button class="p-1 rounded-full text-gray-500 hover:text-gray-700 focus:outline-none">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell"></i>
                        </button>

                        <!-- Profile dropdown -->
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Admin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <?php if (isset($_GET['updated'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Content updated successfully!</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['restored'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Website has been restored to its original state.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['restore_error'])): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Failed to restore the original website. Please try again.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['no_backup'])): ?>
                            <div class="bg-warning-100 border-l-4 border-warning-500 text-warning-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Original backup file not found. Cannot restore.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Website preview card -->
                        <div class="bg-white rounded-2xl shadow-md p-6 mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">Preview your website</h2>
                                <p class="text-sm text-gray-500 mt-1">See how your changes look on the live website</p>
                            </div>
                            <div class="flex space-x-3">
                                <a href="../index.html" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i class="fas fa-eye mr-2"></i> Preview
                                </a>
                                <a href="generate_website.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i class="fas fa-sync-alt mr-2"></i> Regenerate
                                </a>
                            </div>
                        </div>
                        
                        <!-- Main edit form with tabs -->
                        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                            <form method="POST" action="">
                                <div class="flex flex-col md:flex-row">
                                    <!-- Section tabs -->
                                    <div class="w-full md:w-64 bg-white border-r border-gray-200">
                                        <div class="px-4 py-5 border-b border-gray-200">
                                            <h3 class="text-lg font-medium text-gray-900">Website Sections</h3>
                                        </div>
                                        <nav class="px-2 py-3 space-y-1">
                                            <?php foreach ($content as $section => $fields): ?>
                                                <?php 
                                                $icon = isset($sectionIcons[$section]) ? $sectionIcons[$section]['icon'] : 'fa-circle';
                                                $color = isset($sectionIcons[$section]) ? $sectionIcons[$section]['color'] : 'gray'; 
                                                ?>
                                                <a href="#<?php echo $section; ?>" 
                                                   class="section-tab flex items-center px-3 py-3 text-sm font-medium rounded-lg <?php echo $section === 'hero' ? 'active' : ''; ?>"
                                                   data-section="<?php echo $section; ?>">
                                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-600">
                                                        <i class="fas <?php echo $icon; ?>"></i>
                                                    </div>
                                                    <span class="capitalize"><?php echo $section; ?> Section</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </nav>
                                    </div>
                                    
                                    <!-- Section content -->
                                    <div class="flex-1 p-6 overflow-auto max-h-[calc(100vh-13rem)]">
                                        <?php foreach ($content as $section => $fields): ?>
                                            <div id="<?php echo $section; ?>" class="section-content <?php echo $section !== 'hero' ? 'hidden' : ''; ?>">
                                                <div class="flex items-center mb-6">
                                                    <?php 
                                                    $icon = isset($sectionIcons[$section]) ? $sectionIcons[$section]['icon'] : 'fa-circle';
                                                    $color = isset($sectionIcons[$section]) ? $sectionIcons[$section]['color'] : 'gray'; 
                                                    ?>
                                                    <div class="w-10 h-10 mr-3 rounded-lg flex items-center justify-center bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-600">
                                                        <i class="fas <?php echo $icon; ?>"></i>
                                                    </div>
                                                    <h2 class="text-xl font-semibold text-gray-900 capitalize"><?php echo $section; ?> Section</h2>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 gap-6">
                                                    <?php foreach ($fields as $field => $data): ?>
                                                        <div class="bg-gray-50 p-4 rounded-xl">
                                                            <label for="<?php echo $section . '_' . $field; ?>" class="block text-sm font-medium text-gray-700 mb-2">
                                                                <?php echo humanizeFieldName($field); ?>
                                                            </label>
                                                            <?php echo renderInputField($section, $field, $data); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Submit button -->
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div id="upload-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Upload Image</h3>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="upload-form" enctype="multipart/form-data">
                <input type="hidden" id="target-input" name="target">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select image file</label>
                    <input type="file" id="image-upload-input" name="image" accept="image/*" class="w-full">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" onclick="uploadImage()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize all functionality on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Section tab navigation
            const tabs = document.querySelectorAll('.section-tab');
            const sections = document.querySelectorAll('.section-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to current tab
                    this.classList.add('active');
                    
                    // Hide all sections
                    sections.forEach(section => section.classList.add('hidden'));
                    
                    // Show the selected section
                    const sectionId = this.getAttribute('data-section');
                    document.getElementById(sectionId).classList.remove('hidden');
                });
            });
            
            // Preview image when URL changes
            const imageInputs = document.querySelectorAll('input[type="text"]');
            
            imageInputs.forEach(input => {
                // Check if this is an image field (has a preview element)
                const previewElement = input.nextElementSibling?.querySelector('.bg-cover');
                
                if (previewElement) {
                    input.addEventListener('input', function() {
                        previewElement.style.backgroundImage = `url('${this.value}')`;
                    });
                    
                    // Add upload button
                    const parent = input.parentElement;
                    const uploadButton = document.createElement('button');
                    uploadButton.type = 'button';
                    uploadButton.className = 'mt-2 bg-indigo-500 text-white px-3 py-1 rounded-md hover:bg-indigo-600 transition-colors';
                    uploadButton.innerHTML = '<i class="fas fa-upload mr-1"></i> Upload Image';
                    uploadButton.onclick = function() {
                        document.getElementById('image-upload-input').click();
                        document.getElementById('target-input').value = input.id;
                        document.getElementById('upload-modal').classList.remove('hidden');
                    };
                    
                    if (!parent.querySelector('button')) {
                        parent.insertBefore(uploadButton, input.nextElementSibling.nextElementSibling);
                    }
                }
            });
        });
        
        // Image upload functionality
        function uploadImage() {
            const formData = new FormData(document.getElementById('upload-form'));
            const targetInputId = document.getElementById('target-input').value;
            
            fetch('upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Set the uploaded image URL to the target input
                    const targetInput = document.getElementById(targetInputId);
                    targetInput.value = data.url;
                    
                    // Update preview if available
                    const previewElement = targetInput.nextElementSibling?.querySelector('.bg-cover');
                    if (previewElement) {
                        previewElement.style.backgroundImage = `url('${data.url}')`;
                    }
                    
                    closeModal();
                } else {
                    alert('Upload failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during upload.');
            });
        }
        
        function closeModal() {
            document.getElementById('upload-modal').classList.add('hidden');
        }
    </script>
</body>
</html> 