<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if required tables exist
try {
    // Check rsvp table exists
    $rsvpTableExists = $pdo->query("SHOW TABLES LIKE 'rsvp'")->rowCount() > 0;
    if (!$rsvpTableExists) {
        header('Location: create_tables.php');
        exit;
    }
    
    // Try to get count - will error if table structure is wrong
    $rsvp_count = $pdo->query("SELECT COUNT(*) FROM rsvp")->fetchColumn();
} catch (PDOException $e) {
    // If there's a database error, redirect to the table creation script
    header('Location: create_tables.php');
    exit;
}

// Get statistics
$attending_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'yes'")->fetchColumn();
$not_attending_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'no'")->fetchColumn();
$undecided_count = $pdo->query("SELECT COUNT(*) FROM rsvp WHERE attending = 'maybe'")->fetchColumn();

// Get the latest RSVPs
$stmt = $pdo->query("SELECT * FROM rsvp ORDER BY id DESC LIMIT 5");
$latest_rsvps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get dietary restrictions
$stmt = $pdo->query("SELECT dietary_restrictions, COUNT(*) as count FROM rsvp WHERE dietary_restrictions != '' GROUP BY dietary_restrictions ORDER BY count DESC");
$dietary_restrictions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wedding Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.3.0/build/global/luxon.min.js"></script>
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
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
                            <a href="index.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl bg-white bg-opacity-20 text-white">
                                <i class="fas fa-gauge-high mr-3 text-lg"></i>
                                Dashboard
                            </a>
                            <a href="edit_content.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
                                <i class="fas fa-edit mr-3 text-lg"></i>
                                Edit Content
                            </a>
                            <a href="rsvp.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-white hover:bg-opacity-20 transition-all duration-300">
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
                        <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
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
                    <!-- Welcome banner -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-lg overflow-hidden">
                            <div class="px-8 py-8">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-2xl font-bold text-white">Welcome to your Wedding Dashboard</h2>
                                        <p class="mt-1 text-blue-100">Track RSVPs, manage your content, and make your day perfect!</p>
                                    </div>
                                    <div class="hidden lg:block">
                                        <div class="p-4 bg-white bg-opacity-10 rounded-xl">
                                            <span class="text-white text-sm font-medium">Your wedding is in:</span>
                                            <div class="countdown-timer mt-2 flex space-x-2">
                                                <div class="bg-white bg-opacity-20 p-2 rounded-lg text-center min-w-[60px]">
                                                    <span id="days" class="text-xl font-bold text-white">--</span>
                                                    <p class="text-xs text-blue-100">Days</p>
                                                </div>
                                                <div class="bg-white bg-opacity-20 p-2 rounded-lg text-center min-w-[60px]">
                                                    <span id="hours" class="text-xl font-bold text-white">--</span>
                                                    <p class="text-xs text-blue-100">Hours</p>
                                                </div>
                                                <div class="bg-white bg-opacity-20 p-2 rounded-lg text-center min-w-[60px]">
                                                    <span id="minutes" class="text-xl font-bold text-white">--</span>
                                                    <p class="text-xs text-blue-100">Minutes</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <?php if (isset($_GET['restored'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Website has been restored to its original state.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['restore_error'])): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Failed to restore the original website. Please try again.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['no_backup'])): ?>
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg fade-in" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm">Original backup file not found. Cannot restore.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Dashboard content -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <!-- Total RSVPs -->
                            <div class="stat-card bg-white rounded-2xl shadow-md p-6 border-l-4 border-purple-500 fade-in relative overflow-hidden">
                                <div class="absolute top-0 right-0 mt-4 mr-4 bg-purple-100 rounded-full p-2">
                                    <i class="fas fa-users text-purple-500"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-500">Total RSVPs</h3>
                                <p class="mt-1 text-4xl font-extrabold text-gray-900"><?php echo $rsvp_count; ?></p>
                                <div class="mt-4">
                                    <div class="text-sm text-green-600 flex items-center">
                                        <i class="fas fa-chart-line mr-1"></i>
                                        <span>Updated in real-time</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Attending -->
                            <div class="stat-card bg-white rounded-2xl shadow-md p-6 border-l-4 border-green-500 fade-in relative overflow-hidden">
                                <div class="absolute top-0 right-0 mt-4 mr-4 bg-green-100 rounded-full p-2">
                                    <i class="fas fa-check text-green-500"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-500">Attending</h3>
                                <p class="mt-1 text-4xl font-extrabold text-gray-900"><?php echo $attending_count; ?></p>
                                <div class="mt-4">
                                    <div class="text-sm text-green-600 flex items-center">
                                        <i class="fas fa-percentage mr-1"></i>
                                        <span><?php echo $rsvp_count > 0 ? round(($attending_count / $rsvp_count) * 100) : 0; ?>% of total</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Not Attending -->
                            <div class="stat-card bg-white rounded-2xl shadow-md p-6 border-l-4 border-red-500 fade-in relative overflow-hidden">
                                <div class="absolute top-0 right-0 mt-4 mr-4 bg-red-100 rounded-full p-2">
                                    <i class="fas fa-times text-red-500"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-500">Not Attending</h3>
                                <p class="mt-1 text-4xl font-extrabold text-gray-900"><?php echo $not_attending_count; ?></p>
                                <div class="mt-4">
                                    <div class="text-sm text-red-600 flex items-center">
                                        <i class="fas fa-percentage mr-1"></i>
                                        <span><?php echo $rsvp_count > 0 ? round(($not_attending_count / $rsvp_count) * 100) : 0; ?>% of total</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Maybe/Undecided -->
                            <div class="stat-card bg-white rounded-2xl shadow-md p-6 border-l-4 border-yellow-500 fade-in relative overflow-hidden">
                                <div class="absolute top-0 right-0 mt-4 mr-4 bg-yellow-100 rounded-full p-2">
                                    <i class="fas fa-question text-yellow-500"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-500">Undecided</h3>
                                <p class="mt-1 text-4xl font-extrabold text-gray-900"><?php echo $undecided_count; ?></p>
                                <div class="mt-4">
                                    <div class="text-sm text-yellow-600 flex items-center">
                                        <i class="fas fa-percentage mr-1"></i>
                                        <span><?php echo $rsvp_count > 0 ? round(($undecided_count / $rsvp_count) * 100) : 0; ?>% of total</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts and latest RSVPs -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- RSVP Status Chart -->
                            <div class="bg-white rounded-2xl shadow-md p-6 fade-in lg:col-span-1">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">RSVP Status</h3>
                                    <div class="p-1 rounded-md bg-gray-100">
                                        <i class="fas fa-chart-pie text-gray-500"></i>
                                    </div>
                                </div>
                                <div class="h-64 flex items-center justify-center">
                                    <canvas id="rsvpChart"></canvas>
                                </div>
                            </div>

                            <!-- Latest RSVPs -->
                            <div class="bg-white rounded-2xl shadow-md p-6 fade-in lg:col-span-2">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Latest RSVPs</h3>
                                    <a href="rsvp.php" class="text-sm text-indigo-600 hover:text-indigo-800">View all</a>
                                </div>
                                
                                <div class="overflow-hidden">
                                    <div class="overflow-x-auto" id="rsvpContainer">
                                        <?php if (count($latest_rsvps) > 0): ?>
                                            <div class="space-y-4">
                                                <?php foreach($latest_rsvps as $rsvp): ?>
                                                    <div class="bg-gray-50 rounded-xl p-4 flex items-start justify-between hover:bg-gray-100 transition-colors">
                                                        <div class="flex items-start">
                                                            <div class="h-10 w-10 rounded-full flex items-center justify-center mr-3 <?php
                                                                if ($rsvp['attending'] == 'yes') echo 'bg-green-100 text-green-600';
                                                                elseif ($rsvp['attending'] == 'no') echo 'bg-red-100 text-red-600';
                                                                else echo 'bg-yellow-100 text-yellow-600';
                                                            ?>">
                                                                <?php if ($rsvp['attending'] == 'yes'): ?>
                                                                    <i class="fas fa-check"></i>
                                                                <?php elseif ($rsvp['attending'] == 'no'): ?>
                                                                    <i class="fas fa-times"></i>
                                                                <?php else: ?>
                                                                    <i class="fas fa-question"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($rsvp['name']); ?></div>
                                                                <div class="text-sm text-gray-500">
                                                                    <span><?php echo htmlspecialchars($rsvp['email']); ?> • </span>
                                                                    <span class="text-xs px-2 py-1 rounded-full <?php
                                                                        if ($rsvp['attending'] == 'yes') echo 'bg-green-100 text-green-800';
                                                                        elseif ($rsvp['attending'] == 'no') echo 'bg-red-100 text-red-800';
                                                                        else echo 'bg-yellow-100 text-yellow-800';
                                                                    ?>"><?php 
                                                                        if ($rsvp['attending'] == 'yes') echo 'Attending';
                                                                        elseif ($rsvp['attending'] == 'no') echo 'Not attending';
                                                                        else echo 'Maybe';
                                                                    ?></span>
                                                                </div>
                                                                <?php if (!empty($rsvp['message'])): ?>
                                                                    <div class="mt-1 text-sm text-gray-600 italic">"<?php echo htmlspecialchars($rsvp['message']); ?>"</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-gray-400">
                                                            <?php echo date('M j, Y g:i A', strtotime($rsvp['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-gray-500 my-6">
                                                <i class="fas fa-envelope-open-text text-4xl mb-2"></i>
                                                <p>No RSVPs yet</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dietary needs & Quick actions -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                            <!-- Dietary restrictions -->
                            <div class="bg-white rounded-2xl shadow-md p-6 fade-in">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Dietary Needs</h3>
                                    <div class="p-1 rounded-md bg-gray-100">
                                        <i class="fas fa-utensils text-gray-500"></i>
                                    </div>
                                </div>
                                
                                <?php if (count($dietary_restrictions) > 0): ?>
                                    <div class="space-y-3">
                                        <?php foreach($dietary_restrictions as $restriction): ?>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($restriction['dietary_restrictions']); ?></span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><?php echo $restriction['count']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-gray-500 my-6">
                                        <i class="fas fa-clipboard-list text-3xl mb-2"></i>
                                        <p>No dietary restrictions specified</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Quick actions -->
                            <div class="bg-white rounded-2xl shadow-md p-6 fade-in lg:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <a href="edit_content.php" class="bg-indigo-50 hover:bg-indigo-100 transition-colors rounded-xl p-4 text-center">
                                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600 mb-3">
                                            <i class="fas fa-edit text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-indigo-700">Edit Website</h4>
                                    </a>
                                    
                                    <a href="export_rsvp.php" class="bg-green-50 hover:bg-green-100 transition-colors rounded-xl p-4 text-center">
                                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center bg-green-100 text-green-600 mb-3">
                                            <i class="fas fa-file-excel text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-green-700">Export RSVPs</h4>
                                    </a>
                                    
                                    <a href="../index.html" target="_blank" class="bg-blue-50 hover:bg-blue-100 transition-colors rounded-xl p-4 text-center">
                                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center bg-blue-100 text-blue-600 mb-3">
                                            <i class="fas fa-eye text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-blue-700">View Website</h4>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize RSVP Chart
        const ctx = document.getElementById('rsvpChart').getContext('2d');
        const rsvpChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Attending', 'Not Attending', 'Maybe'],
                datasets: [{
                    data: [<?php echo $attending_count; ?>, <?php echo $not_attending_count; ?>, <?php echo $undecided_count; ?>],
                    backgroundColor: [
                        '#10B981', // green
                        '#EF4444', // red
                        '#F59E0B'  // yellow
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Wedding date countdown
        document.addEventListener('DOMContentLoaded', function() {
            // Set the wedding date (you might want to fetch this from your database)
            const weddingDate = "2024-12-15T16:00:00";
            
            function updateCountdown() {
                const now = luxon.DateTime.now();
                const wedding = luxon.DateTime.fromISO(weddingDate);
                const diff = wedding.diff(now, ['days', 'hours', 'minutes', 'seconds']);
                
                document.getElementById('days').textContent = Math.floor(diff.days);
                document.getElementById('hours').textContent = Math.floor(diff.hours);
                document.getElementById('minutes').textContent = Math.floor(diff.minutes);
            }
            
            // Update immediately and then every minute
            updateCountdown();
            setInterval(updateCountdown, 60000);
        });

        // Real-time RSVP updates (placeholder for WebSocket implementation)
        function setupWebSocketConnection() {
            // This is where you would implement real-time updates
            // For now, we'll just simulate refreshing the page every 5 minutes
            setTimeout(() => window.location.reload(), 300000);
        }
        
        setupWebSocketConnection();

        // Add fade-in animation to elements
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html> 