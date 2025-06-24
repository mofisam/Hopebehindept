<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get program ID from request
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid program ID']);
    exit();
}

$programId = (int)$_POST['id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // 1. Get program data (for file deletion later)
    $programQuery = "SELECT featured_image FROM programs WHERE program_id = ?";
    $stmt = $conn->prepare($programQuery);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Program not found");
    }
    
    $program = $result->fetch_assoc();
    $stmt->close();

    // 2. Delete related records in program_category_map
    $deleteCategories = "DELETE FROM program_category_map WHERE program_id = ?";
    $stmt = $conn->prepare($deleteCategories);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    $stmt->close();

    // 3. Delete related records in program_stats
    $deleteStats = "DELETE FROM program_stats WHERE program_id = ?";
    $stmt = $conn->prepare($deleteStats);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    $stmt->close();

    // 4. Handle success stories (set program_id to NULL)
    $updateStories = "UPDATE success_stories SET program_id = NULL WHERE program_id = ?";
    $stmt = $conn->prepare($updateStories);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    $stmt->close();

    // 5. Finally, delete the program itself
    $deleteProgram = "DELETE FROM programs WHERE program_id = ?";
    $stmt = $conn->prepare($deleteProgram);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No program was deleted");
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Delete associated featured image if it exists
    if (!empty($program['featured_image'])) {
        $imagePath = '../uploads/programs/' . $program['featured_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}