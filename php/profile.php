<?php
session_start();
require_once 'config.php';   // ← adjust path if config.php is elsewhere

/* ── Guard ───────────────────────────────────────────── */
if (!isset($_SESSION['user_id'])) {
    die("User not logged in. <a href='login.php'>Login</a>");
}
$user_id = $_SESSION['user_id'];

/* ── 1.  Fetch user profile (name / email) ───────────── */
$userStmt = $conn->prepare("
    SELECT name, email
    FROM users
    WHERE id = ?
    LIMIT 1
");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) die("User record not found.");

$name  = $user['name'];
$email = $user['email'];

/* ── 2.  Fetch latest mood sentiment ─────────────────── */
$moodStmt = $conn->prepare("
    SELECT sentiment, analyzed_at
    FROM mood_entries
    WHERE user_id = ?
    ORDER BY analyzed_at DESC
    LIMIT 1
");
$moodStmt->bind_param("i", $user_id);
$moodStmt->execute();
$lastMood = $moodStmt->get_result()->fetch_assoc();
$moodStmt->close();

$latestSentiment = $lastMood['sentiment'] ?? 'unknown';
$latestTime      = $lastMood['analyzed_at'] ?? '--';

/* Convert sentiment to emoji */
$emoji = ['positive'=>'🙂','neutral'=>'😐','negative'=>'☹️','unknown'=>'❓'][$latestSentiment] ?? '❓';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('https://images.unsplash.com/photo-1457369804613-52c61a468e7d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
        }
      
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-link {
            position: relative;
            transition: color 0.2s ease-in-out;
        }
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #4F46E5;
            border-radius: 2px;
        }
        .nav-link:hover {
            color: #4F46E5;
        }
        .btn-primary {
            background: linear-gradient(45deg, #4F46E5, #7C3AED);
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #4338CA, #6D28D9);
        }
        .mood-badge {
            border-left: 4px solid;
        }
        .mood-positive {
            border-color: #10B981;
        }
        .mood-neutral {
            border-color: #F59E0B;
        }
        .mood-negative {
            border-color: #EF4444;
        }
        .mood-unknown {
            border-color: #6B7280;
        }
        .logo-nav {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen">
    <div class="bg-overlay absolute inset-0 z-0"></div>
    <div class="max-w-6xl mx-auto z-10">
        <!-- Navigation Bar -->
        <nav class="bg-white rounded-xl shadow-lg p-4 mb-10 sticky top-4 z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img src="mindmate.jpg" alt="MindMate Logo" class="logo-nav">
                    <h1 class="text-2xl font-bold text-gray-800">MindMate</h1>
                </div>
                <div class="flex space-x-8 items-center">
                    <a href="dashboard.php" class="text-gray-700 text-lg font-medium nav-link">  Dashboard</a>
                    <a href="mood_history.php" class="text-gray-700 text-lg font-medium nav-link">History</a>
                    <a href="profile.php" class="text-gray-700 text-lg font-medium nav-link active">Profile</a>
                    <a href="index.php" class="text-gray-700 text-lg font-medium nav-link">Journal</a>
                    <a href="logout.php" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Profile Content -->
        <div class="max-w-lg mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-8 fade-in">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-3">👤 Your Profile</h2>
                <div class="space-y-6">
                    <!-- User ID -->
                    <div>
                        <p class="text-sm font-medium text-gray-600">User ID</p>
                        <p class="text-lg text-gray-800"><?php echo htmlspecialchars($user_id); ?></p>
                    </div>
                    <!-- Name -->
                    <div>
                        <p class="text-sm font-medium text-gray-600">Name</p>
                        <p class="text-lg text-gray-800"><?php echo htmlspecialchars($name); ?></p>
                    </div>
                    <!-- Email -->
                    <div>
                        <p class="text-sm font-medium text-gray-600">Email</p>
                        <p class="text-lg text-gray-800"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                    <!-- Latest Mood -->
                    <div>
                        <p class="text-sm font-medium text-gray-600">Latest Mood <?php echo $latestTime !== '--' ? '('.htmlspecialchars(date('F j, Y, g:i A', strtotime($latestTime))).')' : ''; ?></p>
                        <div class="mood-badge <?php echo 'mood-'.strtolower($latestSentiment); ?> flex items-center gap-3 mt-2 p-3 rounded-lg bg-gray-50">
                            <span class="text-2xl"><?php echo $emoji; ?></span>
                            <p class="text-lg font-semibold text-gray-800 capitalize"><?php echo htmlspecialchars($latestSentiment); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Action Button -->
                <div class="mt-8">
                    <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg font-medium transition duration-200 shadow-md flex items-center gap-2">
                        ⬅️ Back to Journal
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>