<?php
include 'config.php';
session_start();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $sql = "SELECT id, name, password, is_verified FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (!$row['is_verified']) {
            $msg = "❌ Please verify your email first.";
        } elseif (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name']    = $row['name'];
            header("Location: index.php");
            exit;
        } else {
            $msg = "❌ Invalid password.";
        }
    } else {
        $msg = "❌ Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindMate – Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
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
            height: 150px; /* Ensure height matches max-width for a square shape */
            border-radius: 50%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image fills the circle without distortion */
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-overlay absolute inset-0 z-0"></div>
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md z-10 fade-in">
        <!-- Logo -->
        <div class="text-center mb-6">
            <img src="mindmate.jpg" alt="MindMate Logo" class="logo mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Welcome Back to MindMate</h1>
            <p class="text-gray-600 mt-2">Log in to continue your mental wellness journey</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
            </div>
            <button type="submit"
                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            No account? <a href="register.php" class="text-green-600 hover:underline">Register</a>
        </p>
    </div>
</body>
</html>