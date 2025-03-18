<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Read the template file
$template_path = '../index.html';
$html_content = file_get_contents($template_path);

// Create a backup of the original file
$backup_path = '../index.html.original';
if (!file_exists($backup_path)) {
    file_put_contents($backup_path, $html_content);
    echo "Original backup created.<br>";
}

// Define the replacements to make (original content => placeholder)
$replacements = [
    // Hero section
    'The Amoureternel of' => '{{hero_subtitle}}',
    'Joshua & Her' => '{{hero_title}}',
    "background-image: url('https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80')" => "background-image: url('{{hero_background_image}}')",
    'December 15, 2024' => '{{date_date_text}}',
    '2024-12-15T16:00:00' => '{{hero_wedding_date}}',
    
    // Date section
    'Save the Date' => '{{date_title}}',
    'at 4:00 in the afternoon' => '{{date_time_text}}',
    'Eko Hotel & Suites' => '{{date_venue_name}}',
    '1415 Adetokunbo Ademola Street' => '{{date_venue_address1}}',
    'Victoria Island, Lagos' => '{{date_venue_address2}}',
    
    // Story section
    'Our Story' => '{{story_title}}',
    "background-image: url('https://images.unsplash.com/photo-1522673607200-164d1b6ce486?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')" => "background-image: url('{{story_first_meeting_image}}')",
    'First Meeting' => '{{story_first_meeting_title}}',
    'We met at a local restaurant in Lekki, Lagos, where Joshua was having lunch with friends. 
                       What started as a casual conversation turned into hours of talking, laughing, and 
                       discovering how much we had in common.' => '{{story_first_meeting_text}}',
                       
    "background-image: url('https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')" => "background-image: url('{{story_proposal_image}}')",
    'The Proposal' => '{{story_proposal_title}}',
    'Two years later, at the same spot where they first met, Joshua got down on one knee and asked Her to 
                       spend the rest of their lives together. The Lagos sunset made the moment even more magical.' => '{{story_proposal_text}}',
    
    // Events section
    'Celebrations' => '{{events_title}}',
    'Traditional Wedding' => '{{events_traditional_title}}',
    '10:00 AM - 4:00 PM' => '{{events_traditional_time}}',
    'Engagement Party' => '{{events_engagement_title}}',
    '6:00 PM - 12:00 AM' => '{{events_engagement_time}}',
    'White Wedding' => '{{events_wedding_title}}',
    '4:00 PM - 12:00 AM' => '{{events_wedding_time}}',
    
    // Gallery section
    'Our Moments' => '{{gallery_title}}',
    'Our First Look' => '{{gallery_caption1}}',
    'The Proposal' => '{{gallery_caption2}}',
    'Engagement Party' => '{{gallery_caption3}}',
    'Pre-Wedding Shoot' => '{{gallery_caption4}}',
    
    // Map section
    'Venue Location' => '{{map_title}}',
    'Eko Hotel & Suites' => '{{map_venue_name}}',
    '1415 Adetokunbo Ademola Street, Victoria Island, Lagos' => '{{map_venue_address}}',
    
    // RSVP section
    'RSVP' => '{{rsvp_title}}',
    
    // Footer
    'Joshua & Her. All rights reserved.' => '{{footer_copyright}}'
];

// Apply the replacements
$modified_content = $html_content;
foreach ($replacements as $original => $placeholder) {
    $modified_content = str_replace($original, $placeholder, $modified_content);
}

// Write the updated content back to the file
if (file_put_contents($template_path, $modified_content)) {
    // Insert these values into the database
    foreach ($replacements as $original => $placeholder) {
        $placeholder = trim($placeholder, '{}');
        list($section, $field_name) = explode('_', $placeholder, 2);
        
        // Check if this entry already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM website_content WHERE section = ? AND field_name = ?");
        $stmt->execute([$section, $field_name]);
        
        if ($stmt->fetchColumn() == 0) {
            // Determine field type
            $field_type = 'text';
            if (strpos($field_name, 'image') !== false) {
                $field_type = 'image';
            } elseif (strpos($field_name, 'date') !== false) {
                $field_type = 'datetime';
            } elseif (strlen($original) > 100) {
                $field_type = 'textarea';
            }
            
            // Insert the content
            $stmt = $pdo->prepare("INSERT INTO website_content (section, field_name, field_value, field_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$section, $field_name, $original, $field_type]);
        }
    }
    
    // Redirect with success message
    header('Location: edit_content.php?template_prepared=1');
    exit;
} else {
    // Redirect with error message
    header('Location: edit_content.php?template_error=1');
    exit;
}
?> 