<?php
// No session check - this is an emergency reset tool

require_once 'config.php';

// Get the paths
$template_path = '../index.html';
$backup_path = '../index.html.original';

// Message to display
$message = '';

// Handle the reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_all') {
    $success = true;
    $errors = [];
    
    // Step 1: Reset the database
    try {
        // Truncate the content table
        $pdo->exec("TRUNCATE TABLE website_content");
        
        // Insert default values from config.php
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
    } catch(PDOException $e) {
        $success = false;
        $errors[] = "Database error: " . $e->getMessage();
    }
    
    // Step 2: Restore the HTML from backup
    if (file_exists($backup_path)) {
        if (!copy($backup_path, $template_path)) {
            $success = false;
            $errors[] = "Failed to restore HTML from backup";
        }
    } else {
        $success = false;
        $errors[] = "Backup file not found at $backup_path";
    }
    
    // Step 3: Generate the website with the default content
    try {
        // Get all content from the database
        $stmt = $pdo->query("SELECT * FROM website_content");
        $all_content = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get the HTML content from the backup file
        $html_content = file_get_contents($backup_path);
        
        // Make replacements for each content item
        foreach ($all_content as $item) {
            $section = $item['section'];
            $field = $item['field_name'];
            $value = $item['field_value'];
            
            // Create the placeholder pattern
            $placeholder = "{{" . $section . "_" . $field . "}}";
            
            // Replace the placeholder
            $html_content = str_replace($placeholder, $value, $html_content);
            
            // Additional replacements for special cases
            // Hero section replacements
            if ($section == 'hero') {
                if ($field == 'background_image') {
                    $pattern = '/<section[^>]*id=["\']hero["|\'][^>]*style=["\']background-image: url\(\'([^\']+)\'\)["|\'][^>]*>/i';
                    $replacement = '<section id="hero" class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url(\'' . $value . '\')">'; 
                    $html_content = preg_replace($pattern, $replacement, $html_content);
                }
            }
        }
        
        // Write the updated content back to the file
        if (!file_put_contents($template_path, $html_content)) {
            $success = false;
            $errors[] = "Failed to write updated content to HTML file";
        }
    } catch(Exception $e) {
        $success = false;
        $errors[] = "Error updating website: " . $e->getMessage();
    }
    
    // Set message based on success/failure
    if ($success) {
        $message = '<div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium">Success! Your website has been completely reset.</p>
                    <p class="text-sm mt-1">Both the database content and HTML template have been restored to their default state.</p>
                </div>
            </div>
        </div>';
    } else {
        $message = '<div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium">Error! Some problems occurred during reset:</p>
                    <ul class="mt-1 ml-6 list-disc text-sm">';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul>
                </div>
            </div>
        </div>';
    }
}

// Check if backup file exists
$backup_exists = file_exists($backup_path);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Wedding Website</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f9fafb;
            background-image: 
                radial-gradient(at 0% 0%, rgba(239, 68, 68, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(245, 158, 11, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(96, 165, 250, 0.1) 0px, transparent 50%);
            background-size: 100% 100%;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        
        .reset-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .btn-danger {
            background: linear-gradient(to right, #EF4444, #F59E0B);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: linear-gradient(to right, #DC2626, #D97706);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.5);
            transform: translateY(-2px);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <div class="flex items-center justify-center mb-6">
                <div class="h-16 w-16 bg-gradient-to-br from-red-500 to-yellow-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-exclamation-triangle text-white text-3xl"></i>
                </div>
                <span class="ml-4 text-4xl font-extrabold text-gray-900">Reset Website</span>
            </div>
            
            <div class="reset-card rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-yellow-500 p-6 text-white">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-bomb mr-3"></i> Complete Website Reset
                    </h1>
                    <p class="mt-2 opacity-90">Reset everything back to the default state</p>
                </div>
                
                <div class="p-8">
                    <?php if (!empty($message)): ?>
                        <?php echo $message; ?>
                    <?php endif; ?>
                    
                    <div class="bg-red-50 p-5 rounded-xl mb-8 border border-red-100">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i> Warning: This is a Destructive Action
                        </h2>
                        <p class="text-gray-700 mb-3">
                            This tool will completely reset your wedding website by:
                        </p>
                        <ul class="list-disc ml-6 text-gray-700 space-y-1">
                            <li>Resetting all database content to default values</li>
                            <li>Restoring the original HTML template</li>
                            <li>Any customizations you've made will be lost</li>
                        </ul>
                        <p class="mt-3 text-red-600 font-medium">
                            Only use this if you want to start completely fresh!
                        </p>
                    </div>
                    
                    <div class="mb-8">
                        <?php if ($backup_exists): ?>
                            <form method="POST" onsubmit="return confirm('WARNING: This will reset EVERYTHING back to default! Are you absolutely sure you want to continue?')">
                                <input type="hidden" name="action" value="reset_all">
                                <button type="submit" class="w-full py-4 btn-danger text-white rounded-xl shadow-lg flex items-center justify-center text-lg font-bold pulse-animation">
                                    <i class="fas fa-trash-alt mr-2"></i> RESET EVERYTHING
                                </button>
                            </form>
                            <p class="text-center text-gray-500 text-sm mt-3">This cannot be undone!</p>
                        <?php else: ?>
                            <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg">
                                <strong>Error:</strong> No backup file was found. Please contact your administrator.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="../index.html" target="_blank" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-globe mr-2"></i> View Website
                        </a>
                        <a href="simple_edit.php" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit Website
                        </a>
                        <a href="restore.php" class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow transition flex items-center">
                            <i class="fas fa-undo-alt mr-2"></i> Restore Only
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 