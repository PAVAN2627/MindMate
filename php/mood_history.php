<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

require_once 'config.php';

$sql = "SELECT sentiment, mood_text, ai_quote, analyzed_at 
        FROM mood_entries 
        WHERE user_id = ? 
        ORDER BY analyzed_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Mood History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('https://images.unsplash.com/photo-1457369804613-52c61a468e7d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-link {
            position: relative;
            transition: color 0.3s ease-in-out;
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
        .mood-entry {
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
        .quote-text {
            background: linear-gradient(45deg, #DBEAFE, #E0F2FE);
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
                    <a href="dashboard.php" class="text-gray-700 text-lg font-medium nav-link">Dashboard</a>
                    <a href="mood_history.php" class="text-gray-700 text-lg font-medium nav-link active">History</a>
                    <a href="profile.php" class="text-gray-700 text-lg font-medium nav-link">Profile</a>
                    <a href="index.php" class="text-gray-700 text-lg font-medium nav-link">Journal</a>
                    <a href="logout.php" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Mood History Content -->
        <div class="space-y-8">
            <div class="bg-white rounded-xl shadow-lg p-8 fade-in">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-3">📜 Your Mood History</h2>
                <?php if ($result->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            // Map sentiment to emoji and class
                            $sentiment = strtolower($row['sentiment']);
                            $emoji = ['positive' => '🙂', 'neutral' => '😐', 'negative' => '☹️'][$sentiment] ?? '❓';
                            $mood_class = ['500e8400-e29b-41d4-a716-446655440000positive' => 'mood-positive', 'neutral' => 'mood-neutral', 'negative' => 'mood-negative'][$sentiment] ?? '';
                            // Format date
                            $date = date('F j, Y, g:i A', strtotime($row['analyzed_at']));
                            ?>
                            <div class="mood-entry <?php echo $mood_class; ?> bg-gray-50 rounded-lg p-6 fade-in">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-2xl"><?php echo $emoji; ?></span>
                                    <h3 class="text-lg font-semibold text-gray-800 capitalize"><?php echo htmlspecialchars($sentiment); ?></h3>
                                </div>
                                <p class="text-gray-600 text-sm"><span class="font-medium">Date:</span> <?php echo htmlspecialchars($date); ?></p>
                                <p class="text-gray-700 mt-2"><span class="font-medium">Your Entry:</span> <?php echo htmlspecialchars($row['mood_text']); ?></p>
                                <div class="quote-text mt-3 p-4 rounded-lg">
                                    <p class="text-gray-700 italic"><span class="font-medium">Quote:</span> "<?php echo htmlspecialchars($row['ai_quote']); ?>"</p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-100 rounded-lg p-6 text-center">
                        <p class="text-gray-600 text-lg italic">No mood entries found.</p>
                        <p class="text-gray-600 mt-2">Start by adding a journal entry to track your mood history!</p>
                        <a href="index.php" class="inline-block mt-4 text-indigo-600 hover:underline font-medium">✍️ Add a Journal Entry</a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Action Button -->
            <div class="flex justify-center fade-in">
                <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg font-medium transition duration-200 shadow-md flex items-center gap-2">
                    ✍️ Add New Mood Entry
                </a>
            </div>
        </div>
    </div>
</body>
</html>