<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/../config/db.php';
include '../include/header.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if program ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: programs.php");
    exit();
}

$programId = (int)$_GET['id'];

// Fetch program to get image path
$query = "SELECT featured_image FROM programs WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $programId);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

// Delete program from database
$query = "DELETE FROM programs WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $programId);
$stmt->execute();
$stmt->close();

// Delete associated image if it exists
if ($program && !empty($program['featured_image']) && file_exists('../' . $program['featured_image'])) {
    unlink('../' . $program['featured_image']);
}

$_SESSION['success_message'] = 'Program deleted successfully!';
header("Location: programs.php");
exit();
?>