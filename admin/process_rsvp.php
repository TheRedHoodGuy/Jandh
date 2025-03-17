<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $guests = $_POST['guests'];
    
    try {
        // Log the attempt
        error_log("Attempting to insert RSVP: " . $name . ", " . $email . ", " . $guests);
        
        $stmt = $pdo->prepare("INSERT INTO rsvps (name, email, guests) VALUES (?, ?, ?)");
        $result = $stmt->execute([$name, $email, $guests]);
        
        // Log the result
        error_log("RSVP Insert Result: " . ($result ? "Success" : "Failed"));
        
        // Redirect back to the main page with success message
        header('Location: ../index.html?rsvp=success');
    } catch(PDOException $e) {
        // Log the error
        error_log("Database Error: " . $e->getMessage());
        
        // Redirect back with error message
        header('Location: ../index.html?rsvp=error');
    }
} else {
    // If someone tries to access this file directly without POST data
    header('Location: ../index.html');
} 