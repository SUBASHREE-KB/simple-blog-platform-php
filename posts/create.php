<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('error', 'You must be logged in to create a post.');
    redirect(BASE_URL . 'auth/login.php');
}

$errors = [];

// Process post creation form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate title
    $title = cleandata($_POST['title']);
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title cannot exceed 255 characters";
    }
    
    // Validate content
    $content = cleandata($_POST['content']);
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    // Process image upload if present
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $image_path = upload_image($_FILES['image']);
        
        if ($image_path === false) {
            $errors[] = "Invalid image. Please upload a valid JPG, PNG, or GIF file under 5MB.";
        }
    }
    
    // If no validation errors, create the post
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $content, $image_path);
        
        if ($stmt->execute()) {
            $post_id = $conn->insert_id;
            set_message('success', 'Post created successfully!');
            redirect(BASE_URL . 'posts/view.php?id=' . $post_id);
        } else {
            $errors[] = "Error creating post. Please try again.";
        }
    }
}

// Include header
$page_title = "Create Post";
include '../includes/header.php';
?>

<div class="post-form-container">
    <h1>Create New Post</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" class="post-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo isset($title) ? $title : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10" required><?php echo isset($content) ? $content : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Image(Optional)</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Accepted formats: JPG, PNG, GIF. Max size: 5MB.</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create Post</button>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>