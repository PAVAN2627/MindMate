<?php
session_start();
include 'config.php';

$email = $_SESSION['pending_email'] ?? null;
if (!$email) die("No pending verification.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND otp_code=? LIMIT 1");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        // mark verified
        $conn->query("UPDATE users SET is_verified=1, otp_code=NULL WHERE id={$row['id']}");
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['name']    = $email;  // fetch real name later
        unset($_SESSION['pending_email']);
        header("Location: login.php");
        exit;
    } else {
        $msg = "Invalid OTP. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Verify Email</title>
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
        .logo {
            max-width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-overlay absolute inset-0 z-0"></div>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md z-10 fade-in">
        <div class="text-center mb-6">
            <img src="mindmate.jpg" alt="MindMate Logo" class="logo mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Verify Your Email</h1>
            <p class="text-gray-600 mt-2">We sent a 6-digit code to <span class="font-medium"><?= htmlspecialchars($email) ?></span>.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700">OTP Code</label>
                <input type="text" name="otp" id="otp" maxlength="6" placeholder="Enter 6-digit code" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-center"
                       pattern="[0-9]{6}" inputmode="numeric">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                Verify
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Back to <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>