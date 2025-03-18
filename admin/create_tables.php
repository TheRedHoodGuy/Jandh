<?php
require_once 'config.php';

try {
    // Create RSVP table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rsvp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        guests INT NOT NULL DEFAULT 1,
        attending ENUM('yes', 'no', 'maybe') NOT NULL,
        message TEXT,
        dietary_restrictions TEXT,
        ticket_sent BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "RSVP table created or already exists.<br>";
    
    // Check if we need to add the ticket_sent column
    $result = $pdo->query("SHOW COLUMNS FROM rsvp LIKE 'ticket_sent'");
    if ($result->rowCount() === 0) {
        $pdo->exec("ALTER TABLE rsvp ADD COLUMN ticket_sent BOOLEAN DEFAULT FALSE");
        echo "Added ticket_sent column to rsvp table.<br>";
    }
    
    // Check if we need to migrate data from the old table to the new one
    $tableExists = $pdo->query("SHOW TABLES LIKE 'rsvps'")->rowCount() > 0;
    
    if ($tableExists) {
        // Check if there's data to migrate
        $dataCount = $pdo->query("SELECT COUNT(*) FROM rsvps")->fetchColumn();
        
        if ($dataCount > 0) {
            // Migrate data from old table to new table
            $pdo->exec("INSERT IGNORE INTO rsvp (id, name, email, guests, ticket_sent, created_at) 
                        SELECT id, name, email, guests, ticket_sent, created_at FROM rsvps");
            
            // Update missing attending field
            $pdo->exec("UPDATE rsvp SET attending = 'yes' WHERE attending IS NULL OR attending = ''");
            
            echo "Migrated $dataCount records from the old 'rsvps' table to the new 'rsvp' table.<br>";
        } else {
            echo "No data to migrate from 'rsvps' table.<br>";
        }
    } else {
        echo "Old 'rsvps' table doesn't exist, no migration needed.<br>";
    }
    
    // Create website_content table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS website_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section VARCHAR(50) NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_value TEXT NOT NULL,
        field_type VARCHAR(50) NOT NULL DEFAULT 'text',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (section, field_name)
    )");
    
    echo "Website content table created or already exists.<br>";
    
    // Add field values for TailwindCSS classes if needed
    $fieldTypeExists = $pdo->query("SHOW COLUMNS FROM website_content LIKE 'field_type'")->rowCount() > 0;
    if (!$fieldTypeExists) {
        $pdo->exec("ALTER TABLE website_content ADD COLUMN field_type VARCHAR(50) NOT NULL DEFAULT 'text'");
        echo "Added field_type column to website_content table.<br>";
    }
    
    // Create log table for website changes
    $pdo->exec("CREATE TABLE IF NOT EXISTS website_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50) NOT NULL,
        section VARCHAR(50) NOT NULL,
        field_name VARCHAR(100),
        old_value TEXT,
        new_value TEXT,
        user VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Website log table created or already exists.<br>";
    
    echo "<h2>Database setup complete!</h2>";
    echo "<p>All tables have been created successfully. You can now <a href='index.php'>go to the dashboard</a>.</p>";
    
    // Add a script to clear cache
    echo "<script>
        // Clear local browser cache for this website
        if (window.caches) {
            caches.keys().then(function(names) {
                for (let name of names)
                    caches.delete(name);
            });
        }
        
        // Clear localStorage
        if (window.localStorage) {
            localStorage.clear();
        }
        
        // Clear sessionStorage
        if (window.sessionStorage) {
            sessionStorage.clear();
        }
    </script>";
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 