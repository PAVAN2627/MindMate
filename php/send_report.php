<?php
session_start();
require_once __DIR__ . '/config.php';   // DB + PHPMailer helpers

/* ── 0. Guard ─────────────────────────────────────────── */
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("User not logged in.");

/* ── 1. Fetch user email + name ───────────────────────── */
$userStmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userRow = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$userRow) die("User not found.");
$recipientName  = $userRow['name'];
$recipientEmail = $userRow['email'];

/* ── 2. Pull last 7 mood entries ──────────────────────── */
$moodStmt = $conn->prepare("
    SELECT analyzed_at AS d, sentiment
    FROM mood_entries
    WHERE user_id = ?
    ORDER BY analyzed_at DESC
    LIMIT 7
");
$moodStmt->bind_param("i", $user_id);
$moodStmt->execute();
$res = $moodStmt->get_result();

$lines = [];
while ($row = $res->fetch_assoc()) {
    $lines[] = $row['d'] . ' : ' . $row['sentiment'];
}
$moodStmt->close();

$lines = array_reverse($lines);     // oldest → newest
if (empty($lines)) die("No mood data available.");

/* ── 3. Send to Flask AI summarizer ───────────────────── */
$flaskUrl = "http://127.0.0.1:5000/summarize_moods";
$payload  = json_encode(['lines' => $lines]);

$ch = curl_init($flaskUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => $payload,
]);
$resp = curl_exec($ch);
curl_close($ch);

if (!$resp) die("Flask summarizer not reachable.");

$summary = json_decode($resp, true)['summary'] ?? 'AI summary unavailable';

/* ── 4. Store summary in DB (create table if missing) ─── */
$conn->query("
  CREATE TABLE IF NOT EXISTS mood_summaries (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      summary TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");

$ins = $conn->prepare("
    INSERT INTO mood_summaries (user_id, summary)
    VALUES (?, ?)
");
$ins->bind_param("is", $user_id, $summary);
$ins->execute();
$ins->close();

/* ── 5. Email the summary ─────────────────────────────── */
$subject = "🧠 Your MindMate Weekly Mood Summary";
$body    = nl2br(
    "Hi $recipientName,\n\n"
  . "Here’s your AI‑generated mood overview:\n\n"
  . "\"$summary\"\n\n"
  . "Stay mindful!\n— MindMate Team"
);

$sendStatus = sendEmail($recipientEmail, $recipientName, $subject, $body);
$message = $sendStatus
    ? "✅ Summary emailed to $recipientEmail and stored in history."
    : "❌ Failed to send email (check SMTP settings).";
$statusClass = $sendStatus ? 'success' : 'error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Sending Mood Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
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
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            min-width: 320px;
            animation: popupFade 2s ease-in-out;
        }
        .popup.success {
            background: linear-gradient(45deg, #10B981, #34D399);
            border-left: 4px solid #065F46;
        }
        .popup.error {
            background: linear-gradient(45deg, #EF4444, #F87171);
            border-left: 4px solid #991B1B;
        }
        @keyframes popupFade {
            0% { opacity: 0; transform: translate(-50%, -40%); }
            10% { opacity: 1; transform: translate(-50%, -50%); }
            90% { opacity: 1; transform: translate(-50%, -50%); }
            100% { opacity: 0; transform: translate(-50%, -40%); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Navigation Bar -->
        <nav class="bg-white rounded-xl shadow-lg p-4 mb-10 sticky top-4 z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🧠</span>
                    <h1 class="text-2xl font-bold text-gray-800">MindMate</h1>
                </div>
                <div class="flex space-x-8 items-center">
                    <a href="dashboard.php" class="text-gray-700 text-lg font-medium nav-link">Dashboard</a>
                    <a href="history.php" class="text-gray-700 text-lg font-medium nav-link">History</a>
                    <a href="profile.php" class="text-gray-700 text-lg font-medium nav-link">Profile</a>
                    <a href="index.php" class="text-gray-700 text-lg font-medium nav-link">Journal</a>
                    <a href="logout.php" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Pop-up Notification -->
        <div class="popup <?php echo $statusClass; ?> rounded-xl shadow-lg p-6 text-white fade-in">
            <div class="flex items-center gap-3">
                <span class="text-2xl"><?php echo $sendStatus ? '✅' : '❌'; ?></span>
                <p class="text-lg font-medium"><?php echo $message; ?></p>
            </div>
        </div>

        <!-- Fallback Content -->
        <div class="bg-white rounded-xl shadow-lg p-8 fade-in text-center">
            <p class="text-gray-600 text-lg">Redirecting to dashboard...</p>
        </div>

        <script>
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        </script>
    </div>
</body>
</html>