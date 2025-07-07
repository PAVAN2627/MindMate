<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

// 1. Get latest mood entry
$sentiment = 'unknown';
$quote     = 'No quote found.';

$sql  = "SELECT sentiment, ai_quote AS quote, analyzed_at 
         FROM mood_entries
         WHERE user_id = ?
         ORDER BY analyzed_at DESC
         LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res  = $stmt->get_result();

if ($res && $res->num_rows) {
    $row       = $res->fetch_assoc();
    $sentiment = $row['sentiment'];
    $quote     = $row['quote'];
}
$stmt->close();

// 2. Get last 7 entries
$labels = [];
$moods  = [];

$sql  = "SELECT DATE(analyzed_at) AS date, sentiment
         FROM mood_entries
         WHERE user_id = ?
         ORDER BY analyzed_at DESC
         LIMIT 7";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res  = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $labels[] = $row['date'];
    $moods[]  = $row['sentiment'];
}
$stmt->close();

// 3. Map mood to numeric (1: negative, 2: neutral, 3: positive)
$sentMap = ['negative' => 1, 'neutral' => 2, 'positive' => 3];
$moodScore = [];
$labelFiltered = [];

foreach ($moods as $i => $m) {
    if (isset($sentMap[$m])) {
        $moodScore[] = $sentMap[$m];
        $labelFiltered[] = $labels[$i];
    }
}

// Reverse for chronological order
$labelFiltered = array_reverse($labelFiltered);
$moodScore     = array_reverse($moodScore);

// 4. Emoji icon
$icon = [
    'positive' => '🙂',
    'neutral'  => '😐',
    'negative' => '☹️'
][$sentiment] ?? '❓';

// 5. Check for support need
$need_support = false;
$sql  = "SELECT sentiment FROM mood_entries
         WHERE user_id = ?
         ORDER BY analyzed_at DESC
         LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$neg_count = 0;
while ($row = $res->fetch_assoc()) {
    if ($row['sentiment'] === 'negative') {
        $neg_count++;
    }
}
$need_support = ($neg_count >= 3);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .quote-box {
            background: linear-gradient(45deg, #E0F2FE, #DBEAFE);
        }
        .support-box {
            background: linear-gradient(45deg, #FFF7ED, #FEF3C7);
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
                    <a href="dashboard.php" class="text-gray-700 text-lg font-medium nav-link active">Dashboard</a>
                    <a href="mood_history.php" class="text-gray-700 text-lg font-medium nav-link">History</a>
                    <a href="profile.php" class="text-gray-700 text-lg font-medium nav-link">Profile</a>
                    <a href="index.php" class="text-gray-700 text-lg font-medium nav-link">Journal</a>
                    <a href="logout.php" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="space-y-8">
            <!-- Welcome and Mood Summary -->
            <div class="bg-white rounded-xl shadow-lg p-6 fade-in">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> 🧠</h2>
                <div class="flex items-center gap-3">
                    <span class="text-3xl"><?= $icon ?></span>
                    <p class="text-lg font-medium text-gray-700">Today's Mood: <span class="capitalize"><?= htmlspecialchars($sentiment) ?></span></p>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="quote-box rounded-xl shadow-lg p-6 fade-in">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">💬 Motivational Quote</h3>
                <p class="text-gray-700 italic">"<?= htmlspecialchars($quote) ?>"</p>
            </div>

            <!-- Weekly Mood Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 fade-in">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Weekly Mood Trend</h3>
                <?php if (empty($moodScore)): ?>
                    <p class="text-gray-600 italic">No mood data available. Add a journal entry to see your mood trend!</p>
                <?php else: ?>
                    <canvas id="moodChart" class="max-w-full"></canvas>
                    <script>
                        const ctx = document.getElementById('moodChart').getContext('2d');
                        let labels = <?= json_encode($labelFiltered) ?>;
                        let scores = <?= json_encode($moodScore) ?>;

                        // If there's only one real point, duplicate it for a horizontal line
                        if (scores.length === 1) {
                            labels = ['', ...labels];
                            scores = [scores[0], ...scores];
                        }

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Mood (1=😔 2=😐 3=😊)',
                                    data: scores,
                                    borderColor: '#4F46E5',
                                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                                    fill: false,
                                    tension: 0.25,
                                    spanGaps: true,
                                    pointRadius: 5,
                                    pointBackgroundColor: scores.map(s =>
                                        s === 3 ? '#10B981' : s === 2 ? '#F59E0B' : '#EF4444')
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        min: 0,
                                        max: 4,
                                        ticks: {
                                            stepSize: 1,
                                            callback: function(value) {
                                                return {1: '😔', 2: '😐', 3: '😊'}[value] || '';
                                            }
                                        },
                                        title: { display: true, text: 'Mood Score' }
                                    },
                                    x: { title: { display: true, text: 'Date' } }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        });
                    </script>
                <?php endif; ?>
            </div>

            <!-- Support and Boost Sections -->
            <?php if ($need_support): ?>
                <div class="support-box rounded-xl shadow-lg p-6 fade-in">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">☹️ Feeling Low Lately?</h3>
                    <p class="text-gray-700 mb-4">You're not alone. We're here for you.</p>
                    <button class="btn-primary text-white px-6 py-3 rounded-lg font-medium transition duration-200 shadow-md"
                            onclick="document.getElementById('supportLinks').classList.toggle('hidden')">
                        Get Help & Uplift 🌈
                    </button>
                    <div id="supportLinks" class="hidden mt-4">
                        <ul class="space-y-2">
                            <li><a href="https://www.7cups.com/" target="_blank" class="text-indigo-600 hover:underline">🗣 Talk to a free trained listener (7 Cups)</a></li>
                            <li><a href="https://www.headspace.com/" target="_blank" class="text-indigo-600 hover:underline">🧘‍♂️ Try guided meditation (Headspace)</a></li>
                            <li><a href="https://www.who.int/health-topics/mental-health" target="_blank" class="text-indigo-600 hover:underline">🌍 WHO Mental Health Resources</a></li>
                        </ul>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 fade-in">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">🌈 Boost Your Mood</h3>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <li><a href="https://youtu.be/roxf7uEun74?si=YrHk1irYLTABwBln" target="_blank" class="text-indigo-600 hover:underline">😂 Watch Funny Videos</a></li>
                        <li><a href="https://www.imdb.com/search/title/?genres=comedy" target="_blank" class="text-indigo-600 hover:underline">🎬 Watch Full Comedy Movies</a></li>
                        <li><a href="https://open.spotify.com/playlist/37i9dQZF1DX1BzILRveYHb" target="_blank" class="text-indigo-600 hover:underline">🎧 Listen to Uplifting Music</a></li>
                        <li><a href="https://www.rd.com/list/short-jokes/" target="_blank" class="text-indigo-600 hover:underline">🃏 Read a Random Joke</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 fade-in">
                <form action="send_report.php" method="post">
                    <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-medium transition duration-200 shadow-md flex items-center gap-2">
                        📤 Send Mood Analysis Report
                    </button>
                </form>
                <a href="index.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                    ✍️ Add Another Mood
                </a>
                <a href="mood_history.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                    📜 View Full Mood History
                </a>
            </div>
        </div>
    </div>
</body>
</html>