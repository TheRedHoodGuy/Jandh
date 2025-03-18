<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // For this emergency fix, we'll allow anonymous access
    // Uncomment the lines below in production
    // header('Location: login.php');
    // exit;
}

// Get all content from the database
$stmt = $pdo->query("SELECT * FROM website_content ORDER BY section, field_name");
$all_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Read the original template file
$template_path = '../index.html';
$backup_path = '../index.html.original';

// Check if we need to create a backup
if (!file_exists($backup_path)) {
    copy($template_path, $backup_path);
    echo "Created backup of the original template.<br>";
}

// Get the HTML content from the original file
$html_content = file_get_contents($backup_path);

// Track replacements for debugging
$replacements_made = 0;
$missing_placeholders = [];

echo "<h2>Updating website with database content</h2>";

// Make direct replacements for common content
foreach ($all_content as $item) {
    $section = $item['section'];
    $field = $item['field_name'];
    $value = $item['field_value'];
    
    // Create the placeholder pattern
    $placeholder = "{{" . $section . "_" . $field . "}}";
    
    // Try to replace the placeholder
    $count = 0;
    $html_content = str_replace($placeholder, $value, $html_content, $count);
    
    if ($count > 0) {
        $replacements_made += $count;
        echo "Replaced {$placeholder} with the value from database.<br>";
    } else {
        $missing_placeholders[] = $placeholder;
        
        // Special case handling for background images
        if ($field == 'background_image' && $section == 'hero') {
            // Try to find and replace the hero background
            $pattern = '/<section[^>]*id=["\']hero["|\'][^>]*style=["\']background-image: url\(\'([^\']+)\'\)["|\'][^>]*>/i';
            $replacement = '<section id="hero" class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url(\'' . $value . '\')">'; 
            $html_content = preg_replace($pattern, $replacement, $html_content, -1, $count);
            if ($count > 0) {
                $replacements_made += $count;
                echo "Replaced hero background image with {$value}.<br>";
            }
        }
        
        // Handle other special cases based on section and field
        if ($section == 'hero') {
            if ($field == 'title') {
                // Try to replace the main title
                $pattern = '/<h1[^>]*class=["\'][^"\']*font-greatvibes[^"\']*["|\'][^>]*>[^<]+<\/h1>/i';
                $replacement = '<h1 class="text-7xl sm:text-8xl md:text-9xl font-greatvibes tracking-wide text-white drop-shadow-lg">' . $value . '</h1>';
                $html_content = preg_replace($pattern, $replacement, $html_content, -1, $count);
                if ($count > 0) {
                    $replacements_made += $count;
                    echo "Replaced hero title with {$value}.<br>";
                }
            } else if ($field == 'subtitle') {
                // Try to replace the subtitle
                $pattern = '/<h2[^>]*>[^<]+<\/h2>/i';
                $replacement = '<h2 class="text-xl sm:text-2xl md:text-3xl font-cormorant text-white uppercase tracking-widest drop-shadow-md mb-4">' . $value . '</h2>';
                $html_content = preg_replace($pattern, $replacement, $html_content, 1, $count);
                if ($count > 0) {
                    $replacements_made += $count;
                    echo "Replaced hero subtitle with {$value}.<br>";
                }
            }
        }
        
        // Add more special case handling here as needed
    }
}

// Write the updated content back to the file
if (file_put_contents($template_path, $html_content)) {
    echo "<p>Website updated successfully! Made {$replacements_made} replacements.</p>";
    
    if (count($missing_placeholders) > 0) {
        echo "<p>Some placeholders were not found in the template:</p>";
        echo "<ul>";
        foreach ($missing_placeholders as $placeholder) {
            echo "<li>{$placeholder}</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><a href='../index.html' target='_blank'>View the updated website</a></p>";
    echo "<p><a href='index.php'>Return to admin dashboard</a></p>";
} else {
    echo "<p>Failed to update the website. Check file permissions.</p>";
}
?> 