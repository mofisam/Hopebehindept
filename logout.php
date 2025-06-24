<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,  // Requires HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Unset all session variables
$_SESSION = array();

// Destroy session
session_destroy();

// Clear all cookies
$cookies = array_keys($_COOKIE);
foreach ($cookies as $cookie) {
    setcookie($cookie, '', time() - 3600, '/');
}

// Prevent caching of sensitive pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login with anti-CSRF token
$token = bin2hex(random_bytes(32));
setcookie('logout_token', $token, 0, '/', '', true, true);
header("Location: /login.php?logout=1&token=".$token);
exit();

?>