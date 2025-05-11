<?php 
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Platform</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">DailyBlogs</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?php echo BASE_URL; ?>posts/create.php">New Post</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
                        <li class="welcome-msg">Welcome, <?php echo get_user_data('username'); ?></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container main-content">
        <?php 
        echo display_message('success');
        echo display_message('error');
        echo display_message('info');
        ?>