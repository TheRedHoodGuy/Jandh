<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Check if the request is a POST and has a file
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

// Define allowed file types
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Check for upload errors
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Upload failed: ';
    switch ($_FILES['image']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $error_message .= 'File too large';
            break;
        case UPLOAD_ERR_PARTIAL:
            $error_message .= 'File only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $error_message .= 'No file was uploaded';
            break;
        default:
            $error_message .= 'Unknown error';
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

// Check file type
$file_info = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $file_info->file($_FILES['image']['tmp_name']);

if (!in_array($mime_type, $allowed_types)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF and WebP are allowed.']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate a unique filename
$file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$new_filename = uniqid() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// Move the uploaded file
if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
    // Return success with the image URL
    $image_url = './uploads/' . $new_filename; // Relative URL for the image
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Image uploaded successfully', 
        'url' => $image_url
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?> 