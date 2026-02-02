<?php
require __DIR__ . '/env.php';
$client_id = $_ENV['GOOGLE_CLIENT_ID'];
$redirect_uri = $_ENV['GOOGLE_REDIRECT_URI'];
$scope = "openid email profile";

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    "client_id" => $client_id,
    "redirect_uri" => $redirect_uri,
    "response_type" => "code",
    "scope" => $scope,
    "access_type" => "online",
    "prompt" => "select_account"
]);

header("Location: $url");
exit;
