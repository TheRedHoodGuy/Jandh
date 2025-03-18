<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rsvp_data_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Set column headers
fputcsv($output, [
    'ID',
    'Name',
    'Email',
    'Attending',
    'Guests',
    'Dietary Restrictions',
    'Message',
    'Ticket Sent',
    'Created At'
]);

// Get filters if any
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$query = "SELECT * FROM rsvp WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $query .= " AND attending = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY created_at DESC";

// Fetch RSVPs
$stmt = $pdo->prepare($query);
$stmt->execute($params);

// Export each row to CSV
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $export_row = [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['attending'],
        $row['guests'],
        $row['dietary_restrictions'],
        $row['message'],
        $row['ticket_sent'] ? 'Yes' : 'No',
        $row['created_at']
    ];
    
    fputcsv($output, $export_row);
}

fclose($output);
exit;
?> 