<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'wedding_db';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create RSVPs table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS rsvps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    guests INT NOT NULL,
    ticket_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Admin credentials (in a real application, these should be stored securely in a database)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Changed to a simple password for testing

// Email configuration
define('WEDDING_EMAIL', 'wedding@example.com'); // Change this to your email 