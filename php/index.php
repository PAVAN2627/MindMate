<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';   // ← contains $conn (mysqli)

// Fake login fallback (remove in production)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// ── Fetch user profile ───────────────────────────────────────
$user_id = $_SESSION['user_id'];
$name  = "Unknown User";
$email = "unknown@example.com";

if ($stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name, $email);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['name'] = $name; // keep name in session for dashboard
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Daily Journal</title>
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
                    <a href="mood_history.php" class="text-gray-700 text-lg font-medium nav-link">History</a>
                    <a href="profile.php" class="text-gray-700 text-lg font-medium nav-link">Profile</a>
                    <a href="index.php" class="text-gray-700 text-lg font-medium nav-link active">Journal</a>
                    <a href="logout.php" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 fade-in">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">👤 Profile</h2>
                <div class="space-y-3">
                    <p><span class="font-medium text-gray-700">Name:</span> <?= htmlspecialchars($name) ?></p>
                    <p><span class="font-medium text-gray-700">Email:</span> <?= htmlspecialchars($email) ?></p>
                </div>
            </div>

            <!-- Journal Section -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-8 fade-in">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">How Are You Feeling Today?</h2>
                <form action="analyze.php" method="POST" class="space-y-6">
                    <div>
                        <textarea name="entry" required rows="8" placeholder="Write how you feel today... Share your thoughts, emotions, or anything on your mind."
                                  class="w-full px-5 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 resize-y text-gray-700 bg-gray-50"></textarea>
                    </div>
                    <button type="submit"
                            class="w-full btn-primary text-white py-3 rounded-lg transition duration-200 font-medium text-lg flex items-center justify-center gap-2 shadow-md">
                        <span>🧾 Analyze & Save Mood</span>
                    </button>
                </form>
                <p class="text-center text-gray-600 mt-6">
                </p>
            </div>
        </div>
    </div>
</body>
</html>