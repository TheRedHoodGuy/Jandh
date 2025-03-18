<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if original backup exists
$original_path = '../index.html.original';
$current_path = '../index.html';

if (file_exists($original_path)) {
    // Read the original content
    $original_content = file_get_contents($original_path);
    
    // Write it back to the current file
    if (file_put_contents($current_path, $original_content)) {
        // Update the database with original content values
        // This ensures future edits start from the original values
        $stmt = $pdo->query("SELECT * FROM website_content");
        $content_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Extract placeholders from content
        $placeholders = [];
        foreach ($content_records as $record) {
            $placeholder = '{{' . $record['section'] . '_' . $record['field_name'] . '}}';
            $placeholders[$placeholder] = [
                'section' => $record['section'],
                'field_name' => $record['field_name'],
                'field_type' => $record['field_type']
            ];
        }
        
        // Look for original values in the HTML
        $updated = false;
        foreach ($placeholders as $placeholder => $info) {
            // Only update text fields for now (images are more complex)
            if ($info['field_type'] === 'text' || $info['field_type'] === 'textarea') {
                // For common fields we know the pattern
                $original_value = null;
                
                if ($info['section'] === 'hero' && $info['field_name'] === 'title') {
                    $original_value = 'Joshua & Her';
                } elseif ($info['section'] === 'hero' && $info['field_name'] === 'subtitle') {
                    $original_value = 'The Amoureternel of';
                } elseif ($info['section'] === 'date' && $info['field_name'] === 'date_text') {
                    $original_value = 'December 15, 2024';
                } elseif ($info['section'] === 'date' && $info['field_name'] === 'venue_name') {
                    $original_value = 'Eko Hotel & Suites';
                } elseif ($info['section'] === 'story' && $info['field_name'] === 'title') {
                    $original_value = 'Our Story';
                }
                
                // If we found an original value, update the database
                if ($original_value) {
                    $update = $pdo->prepare("UPDATE website_content SET field_value = ? WHERE section = ? AND field_name = ?");
                    $update->execute([$original_value, $info['section'], $info['field_name']]);
                    $updated = true;
                }
            }
        }
        
        // Redirect with success message
        header('Location: index.php?restored=1');
        exit;
    } else {
        // Redirect with error
        header('Location: index.php?restore_error=1');
        exit;
    }
} else {
    // Redirect with error if no backup exists
    header('Location: index.php?no_backup=1');
    exit;
}
?> 