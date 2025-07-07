<?php
session_start();
include 'config.php';

/* ---------- Handle form post ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']  ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';

    if (!$name || !$email || !$password) {
        $error = "All fields are required.";
    } else {
        // hash password
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $otp  = random_int(100000, 999999);

        /* insert user */
        $insert = $conn->prepare(
            "INSERT INTO users (name, email, password, otp_code, is_verified) VALUES (?, ?, ?, ?, 0)"
        );
        if (!$insert) {
            $error = "Database error: " . $conn->error;
        } else {
            $insert->bind_param("ssss", $name, $email, $hash, $otp);
            if ($insert->execute()) {
                // send OTP mail
                if (sendOTP($email, $name, $otp)) {
                    $_SESSION['pending_email'] = $email;
                    header("Location: verify.php");
                    exit;
                } else {
                    $error = "Failed to send OTP email. Try again.";
                }
            } else {
                $error = ($conn->errno === 1062)
                    ? "Email already registered."
                    : "Registration failed. " . $conn->error;
            }
            $insert->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Register</title>
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
            <h1 class="text-3xl font-bold text-gray-800">Welcome to MindMate</h1>
            <p class="text-gray-600 mt-2">Create your account to start tracking your mood</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter your name" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" placeholder="Create a password" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                Register
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>