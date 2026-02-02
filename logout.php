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

// Delete token from DB if it exists
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET login_token = NULL, token_expiration = NULL WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Destroy session
session_unset();
session_destroy();

// Delete cookie
setcookie("login_token", "", time() - 3600, "/", "", false, true);

header("Location: login.php");
exit;
