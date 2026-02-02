<?php
session_start();
require __DIR__ . '/env.php';
$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) die("DB error");

// ====== Step 2a: Verify state ======
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die("Invalid state - possible CSRF attack");
}

// ====== Step 2b: Get authorization code ======
if (!isset($_GET['code'])) {
    die("Google login failed");
}

$code = $_GET['code'];

// ====== Step 2c: Exchange code for tokens using PKCE ======
$code_verifier = $_SESSION['oauth_code_verifier'];

$response = file_get_contents(
    "https://oauth2.googleapis.com/token",
    false,
    stream_context_create([
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query([
                "code" => $code,
                "client_id" => $_ENV['GOOGLE_CLIENT_ID'],
                "client_secret" => $_ENV['GOOGLE_CLIENT_SECRET'], // optional for PKCE public clients
                "redirect_uri" => $_ENV['GOOGLE_REDIRECT_URI'],
                "grant_type" => "authorization_code",
                "code_verifier" => $code_verifier
            ])
        ]
    ])
);

$data = json_decode($response, true);
$id_token = $data['id_token'] ?? null;
if (!$id_token) die("Token error");

// ====== Step 2d: Verify ID token including nonce ======
$user = json_decode(file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token), true);

if (!isset($user['nonce']) || $user['nonce'] !== $_SESSION['oauth_nonce']) {
    die("Invalid nonce - token replay attack detected");
}

$email = $user['email'] ?? null;
$name  = $user['name'] ?? 'Google User';
if (!$email) die("Invalid Google account");

// ====== Step 2e: Find or create user in your DB ======
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO users (email, full_name) VALUES (?, ?)");
    $insert->bind_param("ss", $email, $name);
    $insert->execute();
    $user_id = $insert->insert_id;
} else {
    $stmt->bind_result($user_id);
    $stmt->fetch();
}

// ====== Step 2f: Set session and login_token cookie ======
$token  = bin2hex(random_bytes(32));
$expire = date('Y-m-d H:i:s', time() + (86400 * 30));

$update = $conn->prepare("UPDATE users SET login_token = ?, token_expiration = ? WHERE id = ?");
$update->bind_param("ssi", $token, $expire, $user_id);
$update->execute();

setcookie("login_token", $token, time() + (86400 * 30), "/", "", false, true);

$_SESSION['user_id']   = $user_id;
$_SESSION['user_name'] = $name;
$_SESSION['token']     = $token;

// ====== Step 2g: Redirect to success ======
header("Location: success.php");
exit;
