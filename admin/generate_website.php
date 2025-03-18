<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all content from the database
$stmt = $pdo->query("SELECT * FROM website_content ORDER BY section, field_name");
$all_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize content by section
$content = [];
foreach ($all_content as $item) {
    $content[$item['section']][$item['field_name']] = $item['field_value'];
}

// Read the original template file if it exists, otherwise use current file
$template_path = '../index.html';
$backup_path = '../index.html.original';

if (file_exists($backup_path)) {
    $html_content = file_get_contents($backup_path);
} else {
    $html_content = file_get_contents($template_path);
}

// Create a copy to work with
$modified_html = $html_content;

// Track replacements for debugging
$replacements_made = 0;
$total_placeholders = 0;
$missing_placeholders = [];

// First replace standard placeholders
foreach ($content as $section => $fields) {
    foreach ($fields as $field_name => $field_value) {
        // Create the placeholder pattern
        $placeholder = "{{" . $section . "_" . $field_name . "}}";
        $total_placeholders++;
        
        // Check if placeholder exists in the template
        if (strpos($modified_html, $placeholder) !== false) {
            // Replace the placeholder with the actual content
            $count = 0;
            $modified_html = str_replace($placeholder, $field_value, $modified_html, $count);
            $replacements_made += $count;
        } else {
            $missing_placeholders[] = $placeholder;
            
            // Try direct content replacement for common sections
            if ($section == 'hero' && $field_name == 'title') {
                $modified_html = str_replace("Joshua & Her", $field_value, $modified_html, $count);
                $replacements_made += $count;
            } elseif ($section == 'hero' && $field_name == 'subtitle') {
                $modified_html = str_replace("The Amoureternel of", $field_value, $modified_html, $count);
                $replacements_made += $count;
            } elseif ($section == 'date' && $field_name == 'date_text') {
                $modified_html = str_replace("December 15, 2024", $field_value, $modified_html, $count);
                $replacements_made += $count;
            }
            
            // Add more special cases as needed
        }
    }
}

// Now handle special cases for background images
foreach ($content as $section => $fields) {
    foreach ($fields as $field_name => $field_value) {
        if (strpos($field_name, 'image') !== false && !empty($field_value)) {
            // For background images, we need to replace style attributes
            $pattern = "/background-image: url\('.*?'\)/";
            $replacement = "background-image: url('$field_value')";
            
            // For image tags
            $img_pattern = '/<img [^>]*src=["\']([^"\']+)["\'][^>]*>/';
            $img_replacement = '<img src="' . $field_value . '"';
            
            // Apply replacements where appropriate
            if ($section == 'hero' && $field_name == 'background_image') {
                $modified_html = preg_replace($pattern, $replacement, $modified_html, 1, $count);
                $replacements_made += $count;
            } elseif ($section == 'story' && ($field_name == 'first_meeting_image' || $field_name == 'proposal_image')) {
                $modified_html = preg_replace($pattern, $replacement, $modified_html, 1, $count);
                $replacements_made += $count;
            } elseif ($section == 'gallery') {
                // Handle gallery images (likely using img tags)
                if (preg_match($img_pattern, $modified_html, $matches)) {
                    $modified_html = preg_replace($img_pattern, $img_replacement, $modified_html, 1, $count);
                    $replacements_made += $count;
                }
            }
        }
    }
}

// Add timestamp to log
$timestamp = date('Y-m-d H:i:s');
$log = "[$timestamp] $replacements_made of $total_placeholders replacements made.\n";

// Add missing placeholders to the log
if (!empty($missing_placeholders)) {
    $log .= "Missing placeholders:\n";
    foreach ($missing_placeholders as $placeholder) {
        $log .= "  - $placeholder\n";
    }
}

// Create logs directory if it doesn't exist
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}

file_put_contents('../logs/website_generation.log', $log, FILE_APPEND);

// Write the updated content back to the file
if (file_put_contents($template_path, $modified_html)) {
    // Redirect with success message
    header('Location: edit_content.php?generated=1');
    exit;
} else {
    // Redirect with error message
    header('Location: edit_content.php?error=1');
    exit;
}
?> 