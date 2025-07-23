<?php
require_once __DIR__ . '/config/db.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /");
    exit();
}

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Validate required fields
    $required = ['post_id', 'author_name', 'author_email', 'comment_content'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $response['errors'][$field] = "This field is required";
        }
    }

    // Sanitize inputs
    $post_id = (int)$_POST['post_id'];
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $author_name = trim(htmlspecialchars($_POST['author_name']));
    $author_email = filter_var(trim($_POST['author_email']), FILTER_SANITIZE_EMAIL);
    $content = trim(htmlspecialchars($_POST['comment_content']));

    // Additional validation
    if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['author_email'] = "Invalid email format";
    }

    if (strlen($content) < 10) {
        $response['errors']['comment_content'] = "Comment must be at least 10 characters";
    }

    // Check if post exists
    $stmt = $conn->prepare("SELECT post_id FROM blog_posts WHERE post_id = ? AND status = 'published'");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $response['errors']['post_id'] = "Invalid blog post";
    }
    $stmt->close();

    // If no errors, process the comment
    if (empty($response['errors'])) {
        $status = 'pending'; // Default status (change to 'approved' if you want auto-approval)
        
        $stmt = $conn->prepare("INSERT INTO blog_comments 
                               (post_id, parent_id, author_name, author_email, content, status) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $post_id, $parent_id, $author_name, $author_email, $content, $status);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            
            if ($status === 'pending') {
                $response['message'] = "Thank you! Your comment is awaiting moderation.";
            } else {
                $response['message'] = "Thank you for your comment!";
            }
            
            // Get the new comment data for response
            $comment_id = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM blog_comments WHERE comment_id = ?");
            $stmt->bind_param("i", $comment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['comment'] = $result->fetch_assoc();
        } else {
            $response['message'] = "Error saving comment: " . $conn->error;
        }
        $stmt->close();
    } else {
        $response['message'] = "Please correct the errors below";
    }
} catch (Exception $e) {
    $response['message'] = "An error occurred: " . $e->getMessage();
}

// Set JSON header and return response
header('Content-Type: application/json');
echo json_encode($response);