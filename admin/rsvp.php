<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle ticket sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_ticket'])) {
    $rsvp_id = $_POST['rsvp_id'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    
    // Update database to mark ticket as sent
    try {
        $stmt = $pdo->prepare("UPDATE rsvp SET ticket_sent = 1 WHERE id = ?");
        $stmt->execute([$rsvp_id]);
    } catch (PDOException $e) {
        // If the column doesn't exist, ignore the error and continue
        if (strpos($e->getMessage(), "Unknown column 'ticket_sent'") !== false) {
            // Silently continue, the ticket will still be sent
        } else {
            throw $e; // Re-throw any other error
        }
    }
    
    // Send email with ticket
    $to = $email;
    $subject = "Your Wedding Ticket - Amoureturnel";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .ticket { 
                border: 2px solid #4F46E5;
                border-radius: 12px;
                padding: 30px;
                margin: 20px 0;
                text-align: center;
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            }
            .header { 
                font-size: 24px; 
                margin-bottom: 20px;
                color: #4F46E5;
                font-weight: bold;
            }
            .details { margin: 15px 0; }
            .footer {
                font-size: 12px;
                text-align: center;
                margin-top: 30px;
                color: #6b7280;
            }
            .button {
                display: inline-block;
                background: #4F46E5;
                color: white;
                padding: 10px 20px;
                margin: 20px 0;
                border-radius: 5px;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='ticket'>
                <div class='header'>Amoureturnel Wedding</div>
                <div class='details'>Dear $name,</div>
                <div class='details'>Thank you for your RSVP. Here is your digital ticket for the wedding.</div>
                <div class='details'><strong>Date:</strong> December 15, 2024</div>
                <div class='details'><strong>Time:</strong> 4:00 PM</div>
                <div class='details'><strong>Venue:</strong> Eko Hotel & Suites</div>
                <div class='details'>1415 Adetokunbo Ademola Street, Victoria Island, Lagos</div>
                <a href='#' class='button'>Download Ticket</a>
            </div>
            <div class='footer'>
                This is an automated message. Please do not reply to this email.
                <p>© 2024 Amoureturnel. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: wedding@example.com\r\n";
    
    mail($to, $subject, $message, $headers);
    
    header('Location: rsvp.php?success=1');
    exit;
}

// Get filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT * FROM rsvp WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $query .= " AND attending = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY created_at DESC";

// Fetch RSVPs
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rsvps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$total_count = $pdo->query("SELECT COUNT(*) FROM rsvp")->fetchColumn();
$attending_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'yes'")->fetchColumn();
$not_attending_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'no'")->fetchColumn();
$maybe_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'maybe'")->fetchColumn();
$ticket_sent_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE ticket_sent = 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP Management - Amoureturnel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --secondary: #8B5CF6;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
            --light: #F3F4F6;
            --dark: #1F2937;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Dashboard animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out forwards;
        }
        
        /* Card hover effects */
        .rsvp-card {
            transition: all 0.3s ease;
        }
        
        .rsvp-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        
        /* Glass morphism */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="bg-slate-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col h-0 flex-1 bg-gradient-to-b from-indigo-700 to-purple-700 rounded-r-3xl shadow-xl">
                    <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <div class="flex items-center justify-center flex-shrink-0 px-4">
                            <div class="h-12 w-12 bg-white rounded-xl flex items-center justify-center">
                                <i class="fas fa-heart text-indigo-600 text-2xl"></i>
                            </div>
                            <span class="ml-3 text-white text-xl font-extrabold">Amoureturnel</span>
                        </div>
                        <nav class="mt-10 flex-1 px-4 space-y-1">
                            <a href="index.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-gauge-high mr-3 text-lg"></i>
                                Dashboard
                            </a>
                            <a href="edit_content.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-edit mr-3 text-lg"></i>
                                Edit Content
                            </a>
                            <a href="rsvp.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl bg-white bg-opacity-20 text-white">
                                <i class="fas fa-envelope mr-3 text-lg"></i>
                                RSVP Management
                            </a>
                            <a href="../index.html" target="_blank" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-globe mr-3 text-lg"></i>
                                View Website
                            </a>
                        </nav>
                    </div>
                    <div class="p-4">
                        <a href="logout.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                            <i class="fas fa-sign-out-alt mr-3 text-lg"></i>
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <!-- Top navbar -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow-sm">
                <button type="button" class="md:hidden px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars"></i>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h1 class="text-2xl font-semibold text-gray-800">RSVP Management</h1>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <button class="p-1 rounded-full text-gray-500 hover:text-gray-700 focus:outline-none">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell"></i>
                        </button>

                        <!-- Profile dropdown -->
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Admin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <!-- Notification -->
                        <?php if (isset($_GET['success'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Ticket sent successfully!</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Stats Bar -->
                        <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden">
                            <div class="grid grid-cols-1 md:grid-cols-5 divide-y md:divide-y-0 md:divide-x">
                                <div class="p-6 text-center">
                                    <p class="text-sm font-medium text-gray-500">Total RSVPs</p>
                                    <p class="mt-2 text-3xl font-extrabold text-indigo-600"><?php echo $total_count; ?></p>
                                </div>
                                <div class="p-6 text-center">
                                    <p class="text-sm font-medium text-gray-500">Attending</p>
                                    <p class="mt-2 text-3xl font-extrabold text-green-500"><?php echo $attending_count; ?></p>
                                </div>
                                <div class="p-6 text-center">
                                    <p class="text-sm font-medium text-gray-500">Not Attending</p>
                                    <p class="mt-2 text-3xl font-extrabold text-red-500"><?php echo $not_attending_count; ?></p>
                                </div>
                                <div class="p-6 text-center">
                                    <p class="text-sm font-medium text-gray-500">Maybe</p>
                                    <p class="mt-2 text-3xl font-extrabold text-yellow-500"><?php echo $maybe_count; ?></p>
                                </div>
                                <div class="p-6 text-center">
                                    <p class="text-sm font-medium text-gray-500">Tickets Sent</p>
                                    <p class="mt-2 text-3xl font-extrabold text-blue-500"><?php echo $ticket_sent_count; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Search & Filter -->
                        <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center space-y-4 md:space-y-0">
                                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                                    <div>
                                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select id="status-filter" name="status" onchange="applyFilters()" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg">
                                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                            <option value="yes" <?php echo $status_filter === 'yes' ? 'selected' : ''; ?>>Attending</option>
                                            <option value="no" <?php echo $status_filter === 'no' ? 'selected' : ''; ?>>Not Attending</option>
                                            <option value="maybe" <?php echo $status_filter === 'maybe' ? 'selected' : ''; ?>>Maybe</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="relative max-w-xs w-full md:w-64">
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <div class="relative">
                                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or email" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <button onclick="applyFilters()" class="absolute inset-y-0 right-0 px-3 flex items-center bg-indigo-600 text-white rounded-r-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Go
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <a href="export_rsvp.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-file-export mr-2"></i>
                                        Export CSV
                                    </a>
                                    <button type="button" onclick="printRSVPs()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-print mr-2"></i>
                                        Print List
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- RSVP List -->
                        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">RSVP Submissions</h3>
                            </div>
                            
                            <?php if (count($rsvps) > 0): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                                    <?php foreach ($rsvps as $rsvp): ?>
                                        <div class="rsvp-card rounded-xl overflow-hidden border <?php 
                                            if ($rsvp['attending'] == 'yes') echo 'border-green-200 bg-green-50';
                                            elseif ($rsvp['attending'] == 'no') echo 'border-red-200 bg-red-50';
                                            else echo 'border-yellow-200 bg-yellow-50';
                                        ?>">
                                            <div class="p-5">
                                                <div class="flex justify-between items-start">
                                                    <h4 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($rsvp['name']); ?></h4>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php
                                                        if ($rsvp['attending'] == 'yes') echo 'bg-green-100 text-green-800';
                                                        elseif ($rsvp['attending'] == 'no') echo 'bg-red-100 text-red-800';
                                                        else echo 'bg-yellow-100 text-yellow-800';
                                                    ?>">
                                                        <?php 
                                                            if ($rsvp['attending'] == 'yes') echo 'Attending';
                                                            elseif ($rsvp['attending'] == 'no') echo 'Not attending';
                                                            else echo 'Maybe';
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mt-3 space-y-2">
                                                    <p class="text-sm text-gray-600 flex items-center">
                                                        <i class="fas fa-envelope-open-text w-5 text-gray-400"></i>
                                                        <?php echo htmlspecialchars($rsvp['email']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600 flex items-center">
                                                        <i class="fas fa-users w-5 text-gray-400"></i>
                                                        <?php echo $rsvp['guests']; ?> guests
                                                    </p>
                                                    <?php if (!empty($rsvp['dietary_restrictions'])): ?>
                                                        <p class="text-sm text-gray-600 flex items-start">
                                                            <i class="fas fa-utensils w-5 text-gray-400 mt-1"></i>
                                                            <span><?php echo htmlspecialchars($rsvp['dietary_restrictions']); ?></span>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($rsvp['message'])): ?>
                                                        <p class="text-sm text-gray-600 flex items-start">
                                                            <i class="fas fa-comment w-5 text-gray-400 mt-1"></i>
                                                            <span><?php echo htmlspecialchars($rsvp['message']); ?></span>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="text-sm text-gray-500 flex items-center mt-2">
                                                        <i class="fas fa-clock w-5 text-gray-400"></i>
                                                        <?php echo date('M j, Y g:i A', strtotime($rsvp['created_at'])); ?>
                                                    </p>
                                                </div>
                                                
                                                <?php if ($rsvp['attending'] == 'yes' && !$rsvp['ticket_sent']): ?>
                                                    <form method="POST" class="mt-4">
                                                        <input type="hidden" name="rsvp_id" value="<?php echo $rsvp['id']; ?>">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($rsvp['email']); ?>">
                                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($rsvp['name']); ?>">
                                                        <button type="submit" name="send_ticket" class="w-full bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center">
                                                            <i class="fas fa-ticket-alt mr-2"></i>Send Ticket
                                                        </button>
                                                    </form>
                                                <?php elseif ($rsvp['ticket_sent']): ?>
                                                    <div class="mt-4 text-center py-2 bg-gray-100 rounded-md text-sm text-gray-700">
                                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>Ticket sent
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-6 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500">No RSVPs match your filters</p>
                                    <a href="rsvp.php" class="mt-3 inline-block text-indigo-600 hover:text-indigo-800">Clear filters</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Apply filters function
        function applyFilters() {
            const status = document.getElementById('status-filter').value;
            const search = document.getElementById('search').value;
            window.location.href = `rsvp.php?status=${status}&search=${encodeURIComponent(search)}`;
        }
        
        // Handle search on Enter key
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
        
        // Print function
        function printRSVPs() {
            window.print();
        }
        
        // Animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.rsvp-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.animation = `fadeIn 0.3s ease-in-out forwards ${index * 0.05}s`;
            });
        });
    </script>
</body>
</html> 