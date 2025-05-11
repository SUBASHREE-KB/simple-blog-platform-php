<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Destroy the session
session_destroy();

// Set success message in a temporary cookie
setcookie('logout_message', 'You have been successfully logged out!', time() + 5, '/');

// Redirect to home page
redirect(BASE_URL);
?>