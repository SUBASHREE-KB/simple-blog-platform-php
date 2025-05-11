<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('error', 'You must be logged in to delete a post.');
    redirect(BASE_URL . 'auth/login.php');
}

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message('error', 'Invalid post ID.');
    redirect(BASE_URL);
}

$post_id = (int)$_GET['id'];

// Check if user owns the post
if (!is_post_owner($post_id)) {
    set_message('error', 'You can only delete your own posts.');
    redirect(BASE_URL);
}

// Confirm deletion
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // Include header
    $page_title = "Delete Post";
    include '../includes/header.php';
    ?>
    
    <div class="delete-confirmation">
        <h1>Delete Post</h1>
        <p>Are you sure you want to delete this post? This action cannot be undone.</p>
        
        <div class="buttons">
            <a href="<?php echo BASE_URL . 'posts/delete.php?id=' . $post_id . '&confirm=yes'; ?>" class="btn btn-danger">Yes, Delete</a>
            <a href="<?php echo BASE_URL . 'posts/view.php?id=' . $post_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
    
    <?php
    include '../includes/footer.php';
    exit;
}

// Process deletion
// First get the post data to find the image path
$stmt = $conn->prepare("SELECT image_path FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $post = $result->fetch_assoc();
    
    // Delete the image file if it exists
    if (!empty($post['image_path'])) {
        $image_path = UPLOAD_PATH . $post['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete the post from database
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        set_message('success', 'Post deleted successfully!');
    } else {
        set_message('error', 'Error deleting post. Please try again.');
    }
} else {
    set_message('error', 'Post not found.');
}

redirect(BASE_URL);
?>