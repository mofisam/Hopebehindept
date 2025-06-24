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

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$filename = isset($_POST['filename']) ? trim($_POST['filename']) : '';

if ($postId <= 0 || empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    // Update database to remove image reference
    $updateQuery = "UPDATE blog_posts SET featured_image = NULL WHERE post_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();
    
    // Delete the image file
    $imagePath = "../uploads/blog/" . $filename;
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}