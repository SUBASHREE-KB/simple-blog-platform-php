<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check for logout message cookie
if (isset($_COOKIE['logout_message'])) {
    set_message('success', $_COOKIE['logout_message']);
    setcookie('logout_message', '', time() - 3600, '/'); 
}

// Set up pagination
$posts_per_page = 3;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1

// Calculate offset for SQL query
$offset = ($current_page - 1) * $posts_per_page;

// Get total number of posts
$total_result = $conn->query("SELECT COUNT(*) as total FROM posts");
$total_row = $total_result->fetch_assoc();
$total_posts = $total_row['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Ensure current page doesn't exceed total pages
$current_page = min($current_page, $total_pages);

// Get posts for current page with user information
$stmt = $conn->prepare("
    SELECT p.*, u.username 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $posts_per_page, $offset);
$stmt->execute();
$posts_result = $stmt->get_result();

// Include header
$page_title = "Home";
include 'includes/header.php';
?>

<div class="home-container">
    <div class="page-header">
        <h1>Latest Blog Posts</h1>
        <?php if (is_logged_in()): ?>
            <a href="<?php echo BASE_URL; ?>posts/create.php" class="btn btn-primary">Create New Post</a>
        <?php endif; ?>
    </div>
    
    <?php if ($posts_result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <article class="post-card">
                    <?php if (!empty($post['image_path'])): ?>
                        <div class="post-image">
                            <a href="<?php echo BASE_URL . 'posts/view.php?id=' . $post['id']; ?>">
                                <img src="<?php echo UPLOAD_URL . $post['image_path']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-details">
                        <h2 class="post-title">
                            <a href="<?php echo BASE_URL . 'posts/view.php?id=' . $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        
                        <div class="post-meta">
                            <span class="post-author">By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="post-date"><?php echo format_date($post['created_at']); ?></span>
                        </div>
                        
                        <div class="post-excerpt">
                            <?php echo nl2br(htmlspecialchars(excerpt($post['content']))); ?>
                        </div>
                        
                        <a href="<?php echo BASE_URL . 'posts/view.php?id=' . $post['id']; ?>" class="read-more">
                            Read More &raquo;
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <?php echo pagination($total_pages, $current_page, BASE_URL . '?page='); ?>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="no-posts">
            <p>No blog posts yet. Be the first to post!</p>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>posts/create.php" class="btn btn-primary">Create New Post</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary">Login to Create a Post</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>