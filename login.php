<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CBRS502-MiniProject</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <div class="input-group">
        <label>Username</label>
        <input type="text" placeholder="Enter your username">
    </div>

    <div class="input-group password-group">
        <label>Password</label>
        <input type="password" id="password" placeholder="Enter your password">
        <i class="fa-solid fa-eye" id="togglePassword"></i>
    </div>

    <button class="login-btn" onclick="window.location.href='success.php'">Login</button>

    <p class="register-text">
    Don't have an account? 
    <span class="register-link" onclick="window.location.href='sign_up.php'">
        Register
    </span>
    </p>
    
    <div class="divider">OR</div>

    <!-- Google OAuth -->
    <div id="g_id_onload"
         data-client_id="570410618065-cufu9gmtbkuhdgvb1s1vi8k6u4hanup8.apps.googleusercontent.com"
         data-callback="handleCredentialResponse">
    </div>

    <div class="google-btn"
         id="googleSignIn">
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
