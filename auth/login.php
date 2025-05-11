<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(BASE_URL);
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username/email
    $username = cleandata($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username)) {
        $errors[] = "Username or email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if ($password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Set success message
                set_message('success', 'You have been successfully logged in!');
                redirect(BASE_URL);
            } else {
                $errors[] = "Invalid username/email or password";
            }
        } else {
            $errors[] = "Invalid username/email or password";
        }
    }
}

// Include header
$page_title = "Login";
include '../includes/header.php';
?>

<div class="auth-container">
    <h1>Login</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="auth-form">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    
    <p class="auth-links">Don't have an account? <a href="<?php echo BASE_URL; ?>auth/register.php">Register here</a></p>
</div>

<?php include '../includes/footer.php'; ?>