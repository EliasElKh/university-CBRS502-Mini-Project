<?php
session_start();
require __DIR__ . '/env.php';

// OAuth config
$client_id     = $_ENV['GOOGLE_CLIENT_ID'];
$redirect_uri  = $_ENV['GOOGLE_REDIRECT_URI'];
$scope         = "openid email profile";

// ====== Generate security values ======
$state = bin2hex(random_bytes(16));   // CSRF protection
$nonce = bin2hex(random_bytes(16));   // Prevent token replay

// PKCE: generate code_verifier and code_challenge
$code_verifier = bin2hex(random_bytes(32));
$code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

// Store values in session
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_nonce'] = $nonce;
$_SESSION['oauth_code_verifier'] = $code_verifier;

// Build OAuth URL
$params = [
    "client_id" => $client_id,
    "redirect_uri" => $redirect_uri,
    "response_type" => "code",
    "scope" => $scope,
    "state" => $state,
    "nonce" => $nonce,
    "code_challenge" => $code_challenge,
    "code_challenge_method" => "S256",
    "access_type" => "online",
    "prompt" => "select_account"
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

// Redirect to Google
header("Location: $url");
exit;
