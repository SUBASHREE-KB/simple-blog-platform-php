<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'blog_db');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL for your application
define('BASE_URL', 'http://localhost/blog/');

// Path for image uploads
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/blog/assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');

// Maximum file size for uploads (5MB)
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Allowed file types for uploads
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
?>