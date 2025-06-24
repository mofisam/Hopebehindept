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

// Initialize story data
$story = [
    'title' => '',
    'content' => '',
    'author_name' => '',
    'author_image' => '',
    'rating' => 5,
    'program_id' => null,
    'is_featured' => 0
];

// Get all programs for dropdown
$programs = [];
$programQuery = "SELECT program_id, title FROM programs ORDER BY title";
$programResult = $conn->query($programQuery);
while ($row = $programResult->fetch_assoc()) {
    $programs[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $story['title'] = trim($_POST['title']);
    $story['content'] = trim($_POST['content']);
    $story['author_name'] = trim($_POST['author_name']);
    $story['rating'] = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $story['program_id'] = !empty($_POST['program_id']) ? (int)$_POST['program_id'] : null;
    $story['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validate
    $errors = [];
    if (empty($story['title'])) $errors[] = "Title is required";
    if (empty($story['content'])) $errors[] = "Content is required";
    if (empty($story['author_name'])) $errors[] = "Author name is required";
    if ($story['rating'] < 1 || $story['rating'] > 5) $errors[] = "Rating must be between 1 and 5";
    
    if (empty($errors)) {
        // Handle image upload
        $imageUploaded = false;
        if (isset($_FILES['author_image']) && $_FILES['author_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/success-stories/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            $file = $_FILES['author_image'];
            
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('author_') . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $story['author_image'] = $filename;
                    $imageUploaded = true;
                } else {
                    $errors[] = "Failed to move uploaded file";
                }
            } else {
                $errors[] = "Invalid file type or size (max 2MB)";
            }
        }
        
        if (empty($errors)) {
            // Insert the story
            $query = "INSERT INTO success_stories 
                     (title, content, author_name, author_image, rating, program_id, is_featured)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "ssssiii",
                $story['title'],
                $story['content'],
                $story['author_name'],
                $story['author_image'],
                $story['rating'],
                $story['program_id'],
                $story['is_featured']
            );
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Success story added successfully!";
                header("Location: success-stories.php");
                exit();
            } else {
                // Delete uploaded image if insert failed
                if ($imageUploaded && !empty($story['author_image'])) {
                    unlink($uploadDir . $story['author_image']);
                }
                $errors[] = "Error adding story: " . $conn->error;
            }
        }
    }
}

// Set page title
$pageTitle = "Add Success Story";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add Success Story</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="success-stories.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Stories
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?= htmlspecialchars($story['title']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control" id="content" name="content" 
                                              rows="10" required><?= htmlspecialchars($story['content']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="author_name" class="form-label">Author Name *</label>
                                    <input type="text" class="form-control" id="author_name" name="author_name" 
                                           value="<?= htmlspecialchars($story['author_name']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="author_image" class="form-label">Author Image</label>
                                    <input type="file" class="form-control" id="author_image" name="author_image" accept="image/*">
                                    <small class="text-muted">Recommended size: 200x200px (max 2MB)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == $story['rating'] ? 'selected' : '' ?>>
                                                <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Associated Program</label>
                                    <select class="form-select" id="program_id" name="program_id">
                                        <option value="">-- No Program --</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?= $program['program_id'] ?>" 
                                                <?= $story['program_id'] == $program['program_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($program['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           value="1" <?= $story['is_featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Featured Story
                                    </label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Save Story
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Summernote
        $('#content').summernote({
            height: 400,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['link', 'picture', 'video', 'table']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Auto-focus title field on page load
        $('#title').focus();
    });
</script>