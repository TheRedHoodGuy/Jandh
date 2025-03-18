<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Path to the log file
$log_path = '../website_generation.log';

// Clear the log file
$result = file_put_contents($log_path, '');

header('Content-Type: application/json');
if ($result !== false) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear the log file']);
}
?> 