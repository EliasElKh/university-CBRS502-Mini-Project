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


if (isset($_SESSION['user_id'])) {
    header("Location: success.php");
    exit;
}


if (isset($_COOKIE['login_token'])) {
    $token = $_COOKIE['login_token'];

    $stmt = $conn->prepare("
        SELECT id, full_name 
        FROM users 
        WHERE login_token = ? 
        AND token_expiration > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['full_name'];
        $_SESSION['token'] = $token;

        setcookie("login_token", $token, time() + (86400 * 30), "/", "", false, true);

        header("Location: success.php");
        exit;
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $full_name, $hashedPassword);
            $stmt->fetch();

            if ($hashedPassword !== null && password_verify($password, $hashedPassword)) {
                $token = bin2hex(random_bytes(32));
                $expire = date('Y-m-d H:i:s', time() + (86400 * 30));

                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['token'] = $token;

                $update = $conn->prepare("UPDATE users SET login_token = ?, token_expiration = ? WHERE id = ?");
                $update->bind_param("ssi", $token, $expire, $id);
                $update->execute();

                if ($remember) {
                    setcookie("login_token", $token, time() + (86400 * 30), "/", "", false, true);
                }

                header("Location: success.php");
                exit;
            } else {
                $error = "Incorrect password";
            }
        } else {
            $error = "Email not registered";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <link rel="stylesheet" href="style.css">
<style>
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { margin: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); }
.login-container { width: 100%; max-width: 400px; padding: 40px; border-radius: 16px; background: rgba(255,255,255,0.12); backdrop-filter: blur(15px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); color: #fff; }
.login-container h2 { text-align: center; margin-bottom: 30px; }
.input-group { margin-bottom: 20px; }
.input-group label { font-size: 14px; display: block; margin-bottom: 6px; }
.input-group input { width: 100%; padding: 12px; border-radius: 8px; border: none; outline: none; }
.login-btn { width: 100%; padding: 12px; border-radius: 8px; border: none; background: linear-gradient(135deg, #00c6ff, #0072ff); color: white; font-size: 16px; cursor: pointer; }
.login-btn:hover { opacity: 0.9; }
.register-text { text-align: center; margin-top: 15px; font-size: 14px; color: rgba(255,255,255,0.8); }
.register-link { color: #00c6ff; font-weight: 600; cursor: pointer; margin-left: 5px; text-decoration: none; }
.register-link:hover { text-decoration: underline; }
.error { color: #ffb3b3; text-align: center; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="login-container">
<h2>Login</h2>
<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST">
    <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>
    <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div class="input-group">
        <label><input type="checkbox" name="remember"> Remember Me</label>
    </div>
    <button class="login-btn" type="submit">Login</button>
</form>
<div class="register-text">
Don't have an account? <a href="sign_up.php" class="register-link">Register</a>
</div>
<div class="divider">OR</div>

<a href="google_login.php" class="login-btn" style="text-align:center;display:block;text-decoration:none;">
    <i class="fa-brands fa-google"></i> Login with Google
</a>

<!-- <script src="script.js"></script> -->
</body>
</html>
