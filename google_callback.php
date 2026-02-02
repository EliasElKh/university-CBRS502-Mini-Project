<?php
session_start();
require __DIR__ . '/env.php';
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);
if ($conn->connect_error) die("DB error");

if (!isset($_GET['code'])) {
    die("Google login failed");
}

$code = $_GET['code'];

/* Exchange code for token */
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
                "client_secret" => $_ENV['GOOGLE_CLIENT_SECRET'],
                "redirect_uri" => $_ENV['GOOGLE_REDIRECT_URI'],
                "grant_type" => "authorization_code"
            ])
        ]
    ])
);

$data = json_decode($response, true);
$id_token = $data['id_token'] ?? null;
if (!$id_token) die("Token error");

/* Verify user */
$user = json_decode(
    file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=".$id_token),
    true
);

$email = $user['email'] ?? null;
$name  = $user['name'] ?? 'Google User';
if (!$email) die("Invalid Google account");

/* Find or create user */
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $insert = $conn->prepare(
        "INSERT INTO users (email, full_name) VALUES (?, ?)"
    );
    $insert->bind_param("ss", $email, $name);
    $insert->execute();
    $user_id = $insert->insert_id;
} else {
    $stmt->bind_result($user_id);
    $stmt->fetch();
}

/* 🔐 REMEMBER ME (ALWAYS ON FOR GOOGLE) */
$token  = bin2hex(random_bytes(32));
$expire = date('Y-m-d H:i:s', time() + (86400 * 30));

$update = $conn->prepare("
    UPDATE users 
    SET login_token = ?, token_expiration = ?
    WHERE id = ?
");
$update->bind_param("ssi", $token, $expire, $user_id);
$update->execute();

setcookie("login_token", $token, time() + (86400 * 30), "/", "", false, true);

/* Session */
$_SESSION['user_id']   = $user_id;
$_SESSION['user_name'] = $name;
$_SESSION['token']     = $token;

/* Redirect */
header("Location: success.php");
exit;
