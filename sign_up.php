<?php
$error = "";
$success = "";

require __DIR__ . '/env.php';
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($conn->connect_error) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = "Account created successfully";
            } else {
                $error = "Something went wrong";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        /* YOUR CSS – UNCHANGED */
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            color: #fff;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            font-size: 14px;
            display: block;
            margin-bottom: 6px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .login-btn:hover {
            opacity: 0.9;
        }

        .register-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .register-link {
            color: #00c6ff;
            font-weight: 600;
            cursor: pointer;
            margin-left: 5px;
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        .error {
            color: #ffb3b3;
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: #a3ffb3;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
<div class="login-container">
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="login-btn" type="submit">Sign Up</button>
    </form>

    <div class="register-text">
        Already have an account?
        <a href="login.php" class="register-link">Login</a>
    </div>
</div>
</body>
</html>
