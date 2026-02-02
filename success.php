<?php
session_start();
require __DIR__ . '/env.php';
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

// Auto-login via cookie if session expired
if (!isset($_SESSION['user_id']) && isset($_COOKIE['login_token'])) {
    $token = $_COOKIE['login_token'];
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE login_token = ? AND token_expiration > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $full_name);
        $stmt->fetch();
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['token'] = $token;
    } else {
        header("Location: login.php");
        exit;
    }
}

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Success</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Poppins', sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color:white; }
.success-container { text-align:center; background: rgba(255,255,255,0.12); padding:40px; border-radius:16px; backdrop-filter: blur(15px); box-shadow:0 20px 40px rgba(0,0,0,0.4);}
.logout-btn { margin-top:20px; padding:10px 20px; border:none; border-radius:8px; background:#ff4d4d; color:white; cursor:pointer; font-size:16px; }
.logout-btn:hover { opacity:0.9; }
</style>
</head>
<body>
<div class="success-container">
<h2>Logged in Successfully</h2>
<p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
<form action="logout.php" method="POST">
    <button class="logout-btn" type="submit">Logout</button>
</form>
</div>
</body>
</html>
