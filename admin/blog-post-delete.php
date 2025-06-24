<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Check if user is admin
if ($_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit();
}

$postId = (int)$_GET['id'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get featured image filename to delete later
    $imageQuery = "SELECT featured_image FROM blog_posts WHERE post_id = ?";
    $stmt = $conn->prepare($imageQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    
    // Delete post category mappings
    $deleteCats = "DELETE FROM blog_post_categories WHERE post_id = ?";
    $stmt = $conn->prepare($deleteCats);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
    
    // Delete post tag mappings
    $deleteTags = "DELETE FROM blog_post_tags WHERE post_id = ?";
    $stmt = $conn->prepare($deleteTags);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
    
    // Delete post comments
    $deleteComments = "DELETE FROM blog_comments WHERE post_id = ?";
    $stmt = $conn->prepare($deleteComments);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
    
    // Delete the post
    $deletePost = "DELETE FROM blog_posts WHERE post_id = ?";
    $stmt = $conn->prepare($deletePost);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Delete featured image if exists
    if (!empty($post['featured_image'])) {
        $imagePath = "../uploads/blog/" . $post['featured_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}