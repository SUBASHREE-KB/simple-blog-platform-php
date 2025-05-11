<?php
// Clean input data
function cleandata($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Redirect to a specified URL
function redirect($url) {
    header("Location: $url");
    exit();
}

//Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Get logged in user information
function get_user_data($field = null) {
    global $conn;
    
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        if ($field !== null) {
            return $user_data[$field] ?? null;
        }
        return $user_data;
    }
    
    return null;
}

// Check if the current user is the owner of a post
function is_post_owner($post_id) {
    global $conn;
    
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $post = $result->fetch_assoc();
        return $post['user_id'] == $user_id;
    }
    
    return false;
}

// Upload an image file
function upload_image($file) {
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file type
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($file_type, ALLOWED_TYPES)) {
        return false;
    }
    
    // Generate unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = md5(uniqid() . time()) . '.' . $file_ext;
    $target_path = UPLOAD_PATH . $filename;
    
    // Create directories if they don't exist
    if (!file_exists(UPLOAD_PATH) && !is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    // Move the file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $filename;
    }
    
    return false;
}

// Format date for display

function format_date($date) {
    return date('F j, Y \a\t g:i a', strtotime($date));
}

// Create pagination links
function pagination($total_pages, $current_page, $url = '?page=') {
    $html = '<div class="pagination">';
    
    // Previous page link
    if ($current_page > 1) {
        $html .= '<a href="' . $url . ($current_page - 1) . '" class="page-link">&laquo; Previous</a>';
    } else {
        $html .= '<span class="page-link disabled">&laquo; Previous</span>';
    }
    
    // Page number links
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="page-link active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // Next page link
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $url . ($current_page + 1) . '" class="page-link">Next &raquo;</a>';
    } else {
        $html .= '<span class="page-link disabled">Next &raquo;</span>';
    }
    
    $html .= '</div>';
    return $html;
}

// Display flash message
function display_message($name) {
    if (isset($_SESSION[$name])) {
        $message = $_SESSION[$name];
        unset($_SESSION[$name]);
        return '<div class="alert alert-' . $name . '">' . $message . '</div>';
    }
    return '';
}

// Set flash message
function set_message($name, $message) {
    $_SESSION[$name] = $message;
}

// Excerpt function to trim content to a specified length
function excerpt($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}
?>