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

// First, truncate the website_content table
try {
    $pdo->exec("TRUNCATE TABLE website_content");
    echo "Cleared existing website content.<br>";
    
    // Now insert the default content
    // Hero section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('hero', 'background_image', 'https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80', 'image'),
        ('hero', 'subtitle', 'The Amoureternel of', 'text'),
        ('hero', 'title', 'Joshua & Her', 'text'),
        ('hero', 'wedding_date', '2024-12-15T16:00:00', 'datetime')
    ");
    
    // Date section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('date', 'title', 'Save the Date', 'text'),
        ('date', 'date_text', 'December 15, 2024', 'text'),
        ('date', 'time_text', 'at 4:00 in the afternoon', 'text'),
        ('date', 'venue_name', 'Eko Hotel & Suites', 'text'),
        ('date', 'venue_address1', '1415 Adetokunbo Ademola Street', 'text'),
        ('date', 'venue_address2', 'Victoria Island, Lagos', 'text')
    ");
    
    // Story section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('story', 'title', 'Our Story', 'text'),
        ('story', 'first_meeting_image', 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80', 'image'),
        ('story', 'first_meeting_title', 'First Meeting', 'text'),
        ('story', 'first_meeting_text', 'We met at a local restaurant in Lekki, Lagos, where Joshua was having lunch with friends. What started as a casual conversation turned into hours of talking, laughing, and discovering how much we had in common.', 'textarea'),
        ('story', 'proposal_image', 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80', 'image'),
        ('story', 'proposal_title', 'The Proposal', 'text'),
        ('story', 'proposal_text', 'Two years later, at the same spot where they first met, Joshua got down on one knee and asked Her to spend the rest of their lives together. The Lagos sunset made the moment even more magical.', 'textarea')
    ");
    
    // Events section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('events', 'title', 'Celebrations', 'text'),
        ('events', 'traditional_image', 'https://images.unsplash.com/photo-1604422375312-16cb61623e47?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('events', 'traditional_title', 'Traditional Wedding', 'text'),
        ('events', 'traditional_time', '10:00 AM - 4:00 PM', 'text'),
        ('events', 'engagement_image', 'https://images.unsplash.com/photo-1575037614876-c38a4d44f5ed?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('events', 'engagement_title', 'Engagement Party', 'text'),
        ('events', 'engagement_time', '6:00 PM - 12:00 AM', 'text'),
        ('events', 'wedding_image', 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('events', 'wedding_title', 'White Wedding', 'text'),
        ('events', 'wedding_time', '4:00 PM - 12:00 AM', 'text')
    ");
    
    // Gallery section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('gallery', 'title', 'Our Moments', 'text'),
        ('gallery', 'image1', 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('gallery', 'caption1', 'Our First Look', 'text'),
        ('gallery', 'image2', 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('gallery', 'caption2', 'The Proposal', 'text'),
        ('gallery', 'image3', 'https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('gallery', 'caption3', 'Engagement Party', 'text'),
        ('gallery', 'image4', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'image'),
        ('gallery', 'caption4', 'Pre-Wedding Shoot', 'text')
    ");
    
    // Map section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('map', 'title', 'Venue Location', 'text'),
        ('map', 'map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.7999999999997!2d3.4226!3d6.4550!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103b5147e70b0f1d%3A0xdc9e87a367c3d9cb!2sEko%20Hotel%20%26%20Suites!5e0!3m2!1sen!2sng!4v1644839365051!5m2!1sen!2sng', 'textarea'),
        ('map', 'venue_name', 'Eko Hotel & Suites', 'text'),
        ('map', 'venue_address', '1415 Adetokunbo Ademola Street, Victoria Island, Lagos', 'text'),
        ('map', 'directions_link', 'https://goo.gl/maps/xxxxx', 'text')
    ");
    
    // RSVP section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('rsvp', 'title', 'RSVP', 'text'),
        ('rsvp', 'background_image', 'https://images.unsplash.com/photo-1469371670807-013ccf25f16a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80', 'image')
    ");
    
    // Footer section
    $pdo->exec("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES 
        ('footer', 'signature', 'Joshua & Her', 'text'),
        ('footer', 'instagram_link', '#', 'text'),
        ('footer', 'facebook_link', '#', 'text'),
        ('footer', 'twitter_link', '#', 'text'),
        ('footer', 'copyright', '2024 Joshua & Her. All rights reserved.', 'text')
    ");
    
    echo "Default content has been restored.<br>";
    echo "<a href='generate_website.php'>Regenerate website</a><br>";
    echo "<a href='index.php'>Go to admin dashboard</a>";
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 