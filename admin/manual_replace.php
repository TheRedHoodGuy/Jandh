<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['original_content']) || empty($_POST['placeholder'])) {
    header('Location: edit_content.php?manual_error=1');
    exit;
}

// Get form data
$original_content = $_POST['original_content'];
$placeholder = $_POST['placeholder'];

// Extract section and field_name from placeholder
$placeholder_clean = trim($placeholder, '{}');
$parts = explode('_', $placeholder_clean, 2);

if (count($parts) !== 2) {
    header('Location: edit_content.php?manual_error=2');
    exit;
}

$section = $parts[0];
$field_name = $parts[1];

// Read the template file
$template_path = '../index.html';
$html_content = file_get_contents($template_path);

// Check if the original content exists in the file
if (strpos($html_content, $original_content) === false) {
    header('Location: edit_content.php?manual_error=3');
    exit;
}

// Replace the content with the placeholder
$count = 0;
$modified_content = str_replace($original_content, $placeholder, $html_content, $count);

// Add log entry
$timestamp = date('Y-m-d H:i:s');
$log = "[$timestamp] Manual replacement: Replaced '$original_content' with '$placeholder' ($count occurrences).\n";
file_put_contents('../website_generation.log', $log, FILE_APPEND);

// Write the updated content back to the file
if (file_put_contents($template_path, $modified_content) === false) {
    header('Location: edit_content.php?manual_error=4');
    exit;
}

// Check if the content already exists in the database
$stmt = $pdo->prepare("SELECT COUNT(*) FROM website_content WHERE section = ? AND field_name = ?");
$stmt->execute([$section, $field_name]);

// Determine field type
$field_type = 'text';
if (strpos($field_name, 'image') !== false) {
    $field_type = 'image';
} elseif (strpos($field_name, 'date') !== false) {
    $field_type = 'datetime';
} elseif (strlen($original_content) > 100) {
    $field_type = 'textarea';
}

if ($stmt->fetchColumn() == 0) {
    // Insert new entry
    $stmt = $pdo->prepare("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$section, $field_name, $original_content, $field_type]);
} else {
    // Update existing entry
    $stmt = $pdo->prepare("UPDATE website_content SET field_value = ? WHERE section = ? AND field_name = ?");
    $stmt->execute([$original_content, $section, $field_name]);
}

// Redirect with success message
header('Location: edit_content.php?manual_success=1');
exit;
?> 