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

// Get all categories for the form
$categories = [];
$query = "SELECT category_id, name FROM program_categories ORDER BY name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Initialize program data
$program = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'excerpt' => '',
    'status' => 'active',
    'funding_goal' => 0,
    'amount_raised' => 0,
    'progress' => 0,
    'start_date' => '',
    'end_date' => '',
    'is_featured' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'featured_image' => ''
];

$selectedCategories = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $program['title'] = trim($_POST['title']);
    $program['slug'] = trim($_POST['slug']);
    $program['description'] = trim($_POST['description']);
    $program['excerpt'] = trim($_POST['excerpt']);
    $program['status'] = $_POST['status'];
    $program['funding_goal'] = (float)str_replace(',', '', $_POST['funding_goal']);
    $program['amount_raised'] = (float)str_replace(',', '', $_POST['amount_raised']);
    $program['start_date'] = $_POST['start_date'];
    $program['end_date'] = $_POST['end_date'];
    $program['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $program['meta_title'] = trim($_POST['meta_title']);
    $program['meta_description'] = trim($_POST['meta_description']);
    $selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Calculate progress
    if ($program['funding_goal'] > 0) {
        $program['progress'] = min(100, round(($program['amount_raised'] / $program['funding_goal']) * 100));
    } else {
        $program['progress'] = 0;
    }
    
    // Generate slug if empty
    if (empty($program['slug'])) {
        $program['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $program['title'])));
    }
    
    // Validate data
    $errors = [];
    if (empty($program['title'])) $errors[] = "Title is required";
    if (empty($program['description'])) $errors[] = "Description is required";
    if (empty($program['excerpt'])) $errors[] = "Excerpt is required";
    if ($program['funding_goal'] < 0) $errors[] = "Funding goal cannot be negative";
    if ($program['amount_raised'] < 0) $errors[] = "Amount raised cannot be negative";
    
    // Check slug uniqueness
    $check = $conn->prepare("SELECT program_id FROM programs WHERE slug = ?");
    $check->bind_param("s", $program['slug']);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Slug is already in use by another program";
    }
    
    // Handle file upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file = $_FILES['featured_image'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "File too large. Maximum size is 5MB.";
        } else {
            $uploadDir = '../uploads/programs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('program_') . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $program['featured_image'] = $filename;
            } else {
                $errors[] = "Failed to upload featured image";
            }
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Insert program
            $query = "INSERT INTO programs (
                title, slug, description, featured_image, excerpt, 
                status, funding_goal, amount_raised, progress, 
                start_date, end_date, is_featured, meta_title, meta_description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "ssssssddississ", 
                $program['title'], $program['slug'], $program['description'], $program['featured_image'], 
                $program['excerpt'], $program['status'], $program['funding_goal'], $program['amount_raised'], 
                $program['progress'], $program['start_date'], $program['end_date'], $program['is_featured'], 
                $program['meta_title'], $program['meta_description']
            );
            
            if ($stmt->execute()) {
                $programId = $stmt->insert_id;
                
                // Insert category mappings
                if (!empty($selectedCategories)) {
                    $insertCat = $conn->prepare("INSERT INTO program_category_map (program_id, category_id) VALUES (?, ?)");
                    
                    foreach ($selectedCategories as $categoryId) {
                        $insertCat->bind_param("ii", $programId, $categoryId);
                        $insertCat->execute();
                    }
                }
                
                $conn->commit();
                $_SESSION['success_message'] = "Program added successfully!";
                header("Location: programs.php");
                exit();
            } else {
                throw new Exception("Error saving program: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
            
            // Delete uploaded file if transaction failed
            if (!empty($program['featured_image'])) {
                @unlink($uploadDir . $program['featured_image']);
            }
        }
    }
}

// Set page title
$pageTitle = "Add New Program";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Program</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="programs.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Programs
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
                                           value="<?= htmlspecialchars($program['title']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?= htmlspecialchars($program['slug']) ?>" required>
                                        <button class="btn btn-outline-secondary" type="button" id="generate-slug">
                                            <i class="bi bi-arrow-repeat"></i> Generate
                                        </button>
                                    </div>
                                    <small class="text-muted">The URL-friendly version of the name</small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="8" required><?= htmlspecialchars($program['description']) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="excerpt" class="form-label">Excerpt *</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required><?= htmlspecialchars($program['excerpt']) ?></textarea>
                                    <small class="text-muted">A short summary of the program (max 255 characters)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <small class="text-muted">Recommended size: 1200x630px (max 5MB)</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?= htmlspecialchars($program['meta_title']) ?>">
                                        <small class="text-muted">Title for SEO (leave blank to use program title)</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= htmlspecialchars($program['meta_description']) ?></textarea>
                                        <small class="text-muted">Description for SEO (leave blank to use excerpt)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Publish Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Program Status
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?= $program['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="upcoming" <?= $program['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                        <option value="completed" <?= $program['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           value="1" <?= $program['is_featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Featured Program
                                    </label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Save Program
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Funding Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Funding Information
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="funding_goal" class="form-label">Funding Goal (₦) *</label>
                                    <input type="text" class="form-control" id="funding_goal" name="funding_goal" 
                                           value="<?= number_format($program['funding_goal'], 2) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="amount_raised" class="form-label">Amount Raised (₦)</label>
                                    <input type="text" class="form-control" id="amount_raised" name="amount_raised" 
                                           value="<?= number_format($program['amount_raised'], 2) ?>">
                                </div>

                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $program['progress'] ?>%" 
                                         aria-valuenow="<?= $program['progress'] ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $program['progress'] ?>%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dates Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Program Dates
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= htmlspecialchars($program['start_date']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?= htmlspecialchars($program['end_date']) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Categories Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Categories
                            </div>
                            <div class="card-body">
                                <?php foreach ($categories as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="cat-<?= $category['category_id'] ?>" 
                                               name="categories[]" 
                                               value="<?= $category['category_id'] ?>"
                                               <?= in_array($category['category_id'], $selectedCategories) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cat-<?= $category['category_id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Generate slug from title
    document.getElementById('generate-slug').addEventListener('click', function() {
        const title = document.getElementById('title').value;
        if (title) {
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove non-word chars
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/--+/g, '-')     // Replace multiple - with single -
                .trim();                  // Trim - from start and end
            document.getElementById('slug').value = slug;
        }
    });

    // Format currency inputs
    document.getElementById('funding_goal').addEventListener('blur', function() {
        const value = parseFloat(this.value.replace(/,/g, ''));
        if (!isNaN(value)) {
            this.value = value.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            calculateProgress();
        }
    });

    document.getElementById('amount_raised').addEventListener('blur', function() {
        const value = parseFloat(this.value.replace(/,/g, ''));
        if (!isNaN(value)) {
            this.value = value.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            calculateProgress();
        }
    });

    // Calculate and update progress
    function calculateProgress() {
        const goal = parseFloat(document.getElementById('funding_goal').value.replace(/,/g, '')) || 0;
        const raised = parseFloat(document.getElementById('amount_raised').value.replace(/,/g, '')) || 0;
        
        let progress = 0;
        if (goal > 0) {
            progress = Math.min(100, Math.round((raised / goal) * 100));
        }
        
        const progressBar = document.querySelector('.progress-bar');
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.textContent = progress + '%';
    }

    // Auto-focus title field when page loads
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('title').focus();
    });
</script>