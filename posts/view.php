<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message('error', 'Invalid post ID.');
    redirect(BASE_URL);
}

$post_id = (int)$_GET['id'];

// Get post data with username
$stmt = $conn->prepare("
    SELECT p.*, u.username 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    set_message('error', 'Post not found.');
    redirect(BASE_URL);
}

$post = $result->fetch_assoc();

// Include header
$page_title = $post['title'];
include '../includes/header.php';
?>

<div class="post-detail">
    <article class="post">
        <header class="post-header">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span class="post-author">By <?php echo htmlspecialchars($post['username']); ?></span>
                <span class="post-date">Posted on <?php echo format_date($post['created_at']); ?></span>
                <?php if ($post['created_at'] !== $post['updated_at']): ?>
                    <span class="post-updated">(Updated: <?php echo format_date($post['updated_at']); ?>)</span>
                <?php endif; ?>
            </div>
            
            <?php if (is_logged_in() && is_post_owner($post_id)): ?>
                <div class="post-actions">
                    <a href="<?php echo BASE_URL . 'posts/edit.php?id=' . $post_id; ?>" class="btn btn-small">Edit</a>
                    <a href="<?php echo BASE_URL . 'posts/delete.php?id=' . $post_id; ?>" class="btn btn-small btn-danger">Delete</a>
                </div>
            <?php endif; ?>
        </header>
        
        <?php if (!empty($post['image_path'])): ?>
            <div class="post-image">
                <img src="<?php echo UPLOAD_URL . $post['image_path']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
        <?php endif; ?>
        
        <div class="post-content">
            <?php 
            // Format content with paragraphs
            echo nl2br(htmlspecialchars($post['content'])); 
            ?>
        </div>
    </article>
    
    <div class="post-navigation">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">Back to Home</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>