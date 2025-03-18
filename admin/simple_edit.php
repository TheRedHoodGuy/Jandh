<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Update each field
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'field_') === 0) {
                $field_id = substr($key, 6); // Remove 'field_' prefix
                
                $stmt = $pdo->prepare("UPDATE website_content SET field_value = ? WHERE id = ?");
                $stmt->execute([$value, $field_id]);
            }
        }
        
        // Update the website with new content
        updateWebsite();
        
        $message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Changes saved and website updated!</div>';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reset') {
        // Reset the database
        resetDatabase();
        
        // Update the website with default content
        updateWebsite();
        
        $message = '<div class="p-4 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg">Website has been reset to default content.</div>';
    }
}

// Get all website content
$stmt = $pdo->query("SELECT * FROM website_content ORDER BY section, field_name");
$content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group content by section
$sections = [];
foreach ($content as $item) {
    $sections[$item['section']][] = $item;
}

// Function to update the website with content from database
function updateWebsite() {
    global $pdo;
    
    // Get all content from the database
    $stmt = $pdo->query("SELECT * FROM website_content");
    $all_content = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Read the template file
    $template_path = '../index.html';
    $backup_path = '../index.html.original';
    
    // If no backup exists, create one
    if (!file_exists($backup_path)) {
        copy($template_path, $backup_path);
    }
    
    // Get the HTML content from the original file
    $html_content = file_get_contents($backup_path);
    
    // Make replacements
    foreach ($all_content as $item) {
        $section = $item['section'];
        $field = $item['field_name'];
        $value = $item['field_value'];
        
        // Create the placeholder pattern
        $placeholder = "{{" . $section . "_" . $field . "}}";
        
        // Replace the placeholder
        $html_content = str_replace($placeholder, $value, $html_content);
        
        // Handle special cases for direct content replacement
        
        // Hero section replacements
        if ($section == 'hero') {
            if ($field == 'background_image') {
                $pattern = '/<section[^>]*id=["\']hero["|\'][^>]*style=["\']background-image: url\(\'([^\']+)\'\)["|\'][^>]*>/i';
                $replacement = '<section id="hero" class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url(\'' . $value . '\')">'; 
                $html_content = preg_replace($pattern, $replacement, $html_content);
            } elseif ($field == 'title') {
                $pattern = '/<h1[^>]*class=["\'][^"\']*font-greatvibes[^"\']*["|\'][^>]*>[^<]+<\/h1>/i';
                $replacement = '<h1 class="text-7xl sm:text-8xl md:text-9xl font-greatvibes tracking-wide text-white drop-shadow-lg">' . $value . '</h1>';
                $html_content = preg_replace($pattern, $replacement, $html_content);
            } elseif ($field == 'subtitle') {
                $pattern = '/<h2[^>]*>[^<]+<\/h2>/i';
                $replacement = '<h2 class="text-xl sm:text-2xl md:text-3xl font-cormorant text-white uppercase tracking-widest drop-shadow-md mb-4">' . $value . '</h2>';
                $html_content = preg_replace($pattern, $replacement, $html_content, 1);
            } elseif ($field == 'wedding_date') {
                // Convert date to readable format
                $date = new DateTime($value);
                $formatted_date = $date->format('F j, Y');
                $pattern = '/<div[^>]*class="text-[^"\']*"[^>]*>[A-Za-z]+ \d+, \d{4}<\/div>/i';
                $replacement = '<div class="text-2xl font-cormorant text-white my-4">' . $formatted_date . '</div>';
                $html_content = preg_replace($pattern, $replacement, $html_content);
            }
        }
        
        // Date section replacements
        if ($section == 'date') {
            if ($field == 'title') {
                $pattern = '/<h2[^>]*id="date"[^>]*>([^<]+)<\/h2>/i';
                $replacement = '<h2 id="date" class="text-4xl md:text-5xl font-cormorant font-semibold text-center mb-8 tracking-wide text-gray-800">' . $value . '</h2>';
                $html_content = preg_replace($pattern, $replacement, $html_content);
            } elseif ($field == 'date_text') {
                $pattern = '/<p[^>]*class="[^"\']*text-\d+xl[^"\']*"[^>]*>([^<]+)<\/p>/i';
                $replacement = '<p class="text-3xl md:text-4xl font-cormorant font-semibold text-center mb-2">' . $value . '</p>';
                $html_content = preg_replace($pattern, $replacement, $html_content, 1);
            } elseif ($field == 'venue_name') {
                $pattern = '/<h3[^>]*class="[^"\']*font-bold[^"\']*"[^>]*>([^<]+)<\/h3>/i';
                $replacement = '<h3 class="text-2xl md:text-3xl font-cormorant font-bold text-center mt-8 mb-2">' . $value . '</h3>';
                $html_content = preg_replace($pattern, $replacement, $html_content, 1);
            }
        }
        
        // Story section replacements
        if ($section == 'story') {
            if ($field == 'title') {
                $pattern = '/<h2[^>]*id="story"[^>]*>([^<]+)<\/h2>/i';
                $replacement = '<h2 id="story" class="text-4xl md:text-5xl font-cormorant font-semibold text-center mb-16 tracking-wide text-gray-800">' . $value . '</h2>';
                $html_content = preg_replace($pattern, $replacement, $html_content);
            } elseif ($field == 'first_meeting_image') {
                $pattern = '/<div[^>]*class="[^"\']*first-meeting-img[^"\']*"[^>]*style="background-image: url\(\'([^\']+)\'\)"[^>]*>/i';
                $replacement = '<div class="first-meeting-img w-full h-64 md:h-96 bg-cover bg-center rounded-xl timeline-img" style="background-image: url(\'' . $value . '\')">'; 
                $html_content = preg_replace($pattern, $replacement, $html_content);
            } elseif ($field == 'proposal_image') {
                $pattern = '/<div[^>]*class="[^"\']*proposal-img[^"\']*"[^>]*style="background-image: url\(\'([^\']+)\'\)"[^>]*>/i';
                $replacement = '<div class="proposal-img w-full h-64 md:h-96 bg-cover bg-center rounded-xl timeline-img" style="background-image: url(\'' . $value . '\')">'; 
                $html_content = preg_replace($pattern, $replacement, $html_content);
            }
        }
        
        // Gallery images
        if ($section == 'gallery' && strpos($field, 'image') === 0) {
            $num = substr($field, 5); // extract number from 'image1'
            $pattern = '/<img[^>]*data-gallery-img="' . $num . '"[^>]*src="([^"]+)"[^>]*>/i';
            $replacement = '<img data-gallery-img="' . $num . '" class="w-full h-64 object-cover rounded-xl shadow-md" src="' . $value . '" alt="Gallery image ' . $num . '">';
            $html_content = preg_replace($pattern, $replacement, $html_content);
        }
        
        // RSVP section
        if ($section == 'rsvp' && $field == 'background_image') {
            $pattern = '/<section[^>]*id="rsvp"[^>]*style="background-image: url\(\'([^\']+)\'\)"[^>]*>/i';
            $replacement = '<section id="rsvp" class="py-20 bg-cover bg-center relative" style="background-image: url(\'' . $value . '\')">';
            $html_content = preg_replace($pattern, $replacement, $html_content);
        }
    }
    
    // Write the updated content back to the file
    file_put_contents($template_path, $html_content);
}

// Function to reset the database
function resetDatabase() {
    global $pdo;
    
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
}

// Helper function to determine input type
function getInputType($field_type, $field_name) {
    if ($field_type === 'datetime') {
        return 'datetime-local';
    } elseif ($field_type === 'image') {
        return 'url';
    } elseif ($field_name === 'map_embed') {
        return 'textarea';
    } elseif (strpos($field_name, 'text') !== false && strlen($field_name) > 10) {
        return 'textarea';
    } else {
        return 'text';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Wedding Website Editor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .preview-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 50;
        }
        
        .section-card {
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-indigo-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Wedding Website Editor</h1>
            <div class="flex items-center space-x-4">
                <a href="../index.html" target="_blank" class="flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-eye mr-2"></i> Preview
                </a>
                <a href="index.php" class="flex items-center px-4 py-2 bg-indigo-500 rounded-lg hover:bg-indigo-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <a href="logout.php" class="flex items-center px-4 py-2 bg-red-500 rounded-lg hover:bg-red-400 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto py-8 px-4">
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="mb-6 bg-white p-6 rounded-xl shadow-md">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Quick Actions</h2>
                <div class="flex space-x-4">
                    <a href="reset_all.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500 transition flex items-center font-bold animate-pulse">
                        <i class="fas fa-bomb mr-2"></i> Reset Everything
                    </a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to reset all content to defaults?')">
                        <input type="hidden" name="action" value="reset">
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-400 transition flex items-center">
                            <i class="fas fa-undo mr-2"></i> Reset Content
                        </button>
                    </form>
                    <a href="restore.php" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-400 transition flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i> Restore Site
                    </a>
                    <a href="../index.html" target="_blank" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-400 transition flex items-center">
                        <i class="fas fa-globe mr-2"></i> View Website
                    </a>
                </div>
            </div>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="update">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($sections as $section_name => $section_items): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden section-card">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4">
                            <h3 class="text-xl font-bold text-white capitalize"><?php echo $section_name; ?> Section</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <?php foreach ($section_items as $item): ?>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 capitalize">
                                        <?php echo str_replace('_', ' ', $item['field_name']); ?>
                                    </label>
                                    
                                    <?php if (getInputType($item['field_type'], $item['field_name']) === 'textarea'): ?>
                                        <textarea 
                                            name="field_<?php echo $item['id']; ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            rows="3"
                                        ><?php echo htmlspecialchars($item['field_value']); ?></textarea>
                                    <?php else: ?>
                                        <input 
                                            type="<?php echo getInputType($item['field_type'], $item['field_name']); ?>" 
                                            name="field_<?php echo $item['id']; ?>" 
                                            value="<?php echo htmlspecialchars($item['field_value']); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                    <?php endif; ?>
                                    
                                    <?php if ($item['field_type'] === 'image'): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo htmlspecialchars($item['field_value']); ?>" alt="Preview" class="h-20 rounded border border-gray-200">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-8 flex justify-center">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-500 transition shadow-lg flex items-center text-lg">
                    <i class="fas fa-save mr-2"></i> Save Changes & Update Website
                </button>
            </div>
        </form>
        
        <a href="../index.html" target="_blank" class="preview-btn px-4 py-3 bg-green-600 text-white rounded-full hover:bg-green-500 transition shadow-lg">
            <i class="fas fa-eye"></i> Preview
        </a>
    </div>
    
    <script>
        // Auto-expand textareas based on content
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
                
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
        });
    </script>
</body>
</html> 