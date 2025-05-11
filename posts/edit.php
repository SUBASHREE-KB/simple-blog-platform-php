<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('error', 'You must be logged in to edit a post.');
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
    set_message('error', 'You can only edit your own posts.');
    redirect(BASE_URL);
}

// Get post data
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    set_message('error', 'Post not found.');
    redirect(BASE_URL);
}

$post = $result->fetch_assoc();
$errors = [];
$title = $post['title'];
$content = $post['content'];
$image_path = $post['image_path'];

// Handle form submission
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

    // Handle image
    if (!empty($_FILES['image']['name'])) {
        $new_image = upload_image($_FILES['image']);
        if ($new_image === false) {
            $errors[] = "Invalid image. Upload JPG/PNG/GIF under 5MB.";
        } else {
            // Remove old image
            if (!empty($post['image_path']) && file_exists(UPLOAD_PATH . $post['image_path'])) {
                unlink(UPLOAD_PATH . $post['image_path']);
            }
            $image_path = $new_image;
        }
    }

    // Update post
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $image_path, $post_id);
        if ($stmt->execute()) {
            set_message('success', 'Post updated successfully!');
            redirect(BASE_URL . 'posts/view.php?id=' . $post_id);
        } else {
            $errors[] = "Error updating post. Try again.";
        }
    }
}

// Render HTML after variables are set
$page_title = "Edit Post";
include '../includes/header.php';
?>

<div class="post-form-container">
    <h1>Edit Post</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $post_id); ?>" method="post" enctype="multipart/form-data" class="post-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Featured Image (Optional)</label>
            <?php if (!empty($post['image_path'])): ?>
                <div class="current-image">
                    <p>Current Image:</p>
                    <img src="<?php echo UPLOAD_URL . $post['image_path']; ?>" alt="Current image" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Leave empty to keep current image. Max 5MB.</small>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Post</button>
            <a href="<?php echo BASE_URL . 'posts/view.php?id=' . $post_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
