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
    $stmt = $pdo->prepare("UPDATE rsvps SET ticket_sent = 1 WHERE id = ?");
    $stmt->execute([$rsvp_id]);
    
    // Send email with ticket
    $to = $email;
    $subject = "Your Wedding Ticket - Joshua & Her's Wedding";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .ticket { 
                border: 2px solid #000;
                padding: 20px;
                margin: 20px 0;
                text-align: center;
            }
            .header { font-size: 24px; margin-bottom: 20px; }
            .details { margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='ticket'>
            <div class='header'>Joshua & Her's Wedding</div>
            <div class='details'>Dear $name,</div>
            <div class='details'>Thank you for your RSVP. Here is your ticket for the wedding.</div>
            <div class='details'>Date: December 15, 2024</div>
            <div class='details'>Time: 4:00 PM</div>
            <div class='details'>Venue: Eko Hotel & Suites</div>
            <div class='details'>1415 Adetokunbo Ademola Street, Victoria Island, Lagos</div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: wedding@example.com\r\n";
    
    mail($to, $subject, $message, $headers);
    
    header('Location: index.php?success=1');
    exit;
}

// Fetch all RSVPs
$stmt = $pdo->query("SELECT * FROM rsvps ORDER BY created_at DESC");
$rsvps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding RSVP Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s;
        }
        .sidebar:hover {
            width: 250px;
        }
        .sidebar:hover .sidebar-text {
            display: inline;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar bg-blue-800 text-white w-16 hover:w-64 transition-all duration-300">
            <div class="p-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-heart text-2xl"></i>
                    <span class="sidebar-text hidden">Wedding Admin</span>
                </div>
            </div>
            <nav class="mt-8">
                <a href="index.php" class="flex items-center px-4 py-3 bg-blue-900">
                    <i class="fas fa-home"></i>
                    <span class="sidebar-text hidden ml-4">Dashboard</span>
                </a>
                <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text hidden ml-4">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Bar -->
            <div class="bg-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 py-4">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold text-gray-800">RSVP Dashboard</h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-600">
                                <i class="fas fa-user-circle mr-2"></i>Admin
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
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

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Total RSVPs</p>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo count($rsvps); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-paper-plane text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Tickets Sent</p>
                                <p class="text-2xl font-semibold text-gray-800">
                                    <?php echo count(array_filter($rsvps, function($rsvp) { return $rsvp['ticket_sent']; })); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Pending Tickets</p>
                                <p class="text-2xl font-semibold text-gray-800">
                                    <?php echo count(array_filter($rsvps, function($rsvp) { return !$rsvp['ticket_sent']; })); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RSVP Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($rsvps as $rsvp): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition-all duration-300 hover:shadow-lg <?php echo $rsvp['ticket_sent'] ? 'border-l-4 border-green-500' : 'border-l-4 border-yellow-500'; ?>">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($rsvp['name']); ?></h3>
                                    <?php if ($rsvp['ticket_sent']): ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                            <i class="fas fa-check mr-1"></i>Ticket Sent
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="space-y-3 text-gray-600">
                                    <p class="flex items-center">
                                        <i class="fas fa-envelope w-6 text-blue-500"></i>
                                        <?php echo htmlspecialchars($rsvp['email']); ?>
                                    </p>
                                    <p class="flex items-center">
                                        <i class="fas fa-users w-6 text-green-500"></i>
                                        <?php echo $rsvp['guests']; ?> Guests
                                    </p>
                                    <p class="flex items-center">
                                        <i class="fas fa-calendar w-6 text-purple-500"></i>
                                        <?php echo date('M d, Y', strtotime($rsvp['created_at'])); ?>
                                    </p>
                                </div>

                                <?php if (!$rsvp['ticket_sent']): ?>
                                    <form method="POST" class="mt-6">
                                        <input type="hidden" name="rsvp_id" value="<?php echo $rsvp['id']; ?>">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($rsvp['email']); ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($rsvp['name']); ?>">
                                        <button type="submit" name="send_ticket" 
                                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                            <i class="fas fa-paper-plane mr-2"></i>Send Ticket
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 