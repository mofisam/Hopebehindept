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

// Check if program ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: programs.php");
    exit();
}

$programId = (int)$_GET['id'];

// Fetch program data
$query = "SELECT * FROM programs WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $programId);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

if (!$program) {
    header("Location: programs.php");
    exit();
}

// Initialize variables
$errors = [];
$formData = $program;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['excerpt'] = trim($_POST['excerpt'] ?? '');
    $formData['funding_goal'] = filter_var($_POST['funding_goal'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $formData['status'] = in_array($_POST['status'] ?? '', ['active', 'upcoming', 'completed']) ? $_POST['status'] : 'active';
    $formData['start_date'] = $_POST['start_date'] ?? '';
    $formData['end_date'] = $_POST['end_date'] ?? '';
    $featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validate required fields
    if (empty($formData['title'])) {
        $errors['title'] = 'Program title is required';
    }
    if (empty($formData['description'])) {
        $errors['description'] = 'Program description is required';
    }
    if (empty($formData['excerpt'])) {
        $errors['excerpt'] = 'Short excerpt is required';
    }
    if ($formData['funding_goal'] <= 0) {
        $errors['funding_goal'] = 'Funding goal must be greater than 0';
    }

    // Handle file upload if new image is provided
    $imagePath = $program['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $_FILES['featured_image']['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($detectedType, $allowedTypes)) {
            $errors['featured_image'] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        } elseif ($_FILES['featured_image']['size'] > 5 * 1024 * 1024) { // 5MB
            $errors['featured_image'] = 'File size too large. Maximum 5MB allowed.';
        } else {
            $uploadDir = '../uploads/programs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('program_') . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $destination)) {
                // Delete old image if it exists
                if ($imagePath && file_exists('../' . $imagePath)) {
                    unlink('../' . $imagePath);
                }
                $imagePath = 'uploads/programs/' . $filename;
            } else {
                $errors['featured_image'] = 'Failed to upload image';
            }
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        $query = "UPDATE programs SET
                    title = ?, 
                    description = ?, 
                    excerpt = ?, 
                    featured_image = ?, 
                    funding_goal = ?, 
                    status = ?, 
                    start_date = ?, 
                    end_date = ?, 
                    is_featured = ?,
                    updated_at = NOW()
                  WHERE program_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "ssssdsssii",
            $formData['title'],
            $formData['description'],
            $formData['excerpt'],
            $imagePath,
            $formData['funding_goal'],
            $formData['status'],
            $formData['start_date'],
            $formData['end_date'],
            $featured,
            $programId
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Program updated successfully!';
            header("Location: programs.php");
            exit();
        } else {
            $errors['database'] = 'Error updating program: ' . $conn->error;
        }
    }
}
include '../include/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h2 class="h4 mb-0">Edit Program</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errors['database']) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Program Title</label>
                            <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                                   id="title" name="title" value="<?= htmlspecialchars($formData['title']) ?>" required>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Short Excerpt</label>
                            <textarea class="form-control <?= isset($errors['excerpt']) ? 'is-invalid' : '' ?>" 
                                      id="excerpt" name="excerpt" rows="3" required><?= htmlspecialchars($formData['excerpt']) ?></textarea>
                            <small class="text-muted">A brief summary (1-2 sentences) displayed on program cards</small>
                            <?php if (isset($errors['excerpt'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['excerpt']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description</label>
                            <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                                      id="description" name="description" rows="6" required><?= htmlspecialchars($formData['description']) ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control <?= isset($errors['featured_image']) ? 'is-invalid' : '' ?>" 
                                   id="featured_image" name="featured_image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                            <?php if ($program['featured_image']): ?>
                                <div class="mt-2">
                                    <img src="../<?= htmlspecialchars($program['featured_image']) ?>" alt="Current image" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <?php if (isset($errors['featured_image'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['featured_image']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="funding_goal" class="form-label">Funding Goal (₦)</label>
                                <input type="number" step="0.01" class="form-control <?= isset($errors['funding_goal']) ? 'is-invalid' : '' ?>" 
                                       id="funding_goal" name="funding_goal" value="<?= htmlspecialchars($formData['funding_goal']) ?>" required>
                                <?php if (isset($errors['funding_goal'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['funding_goal']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="upcoming" <?= $formData['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                    <option value="completed" <?= $formData['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($formData['start_date']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($formData['end_date']) ?>">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" <?= $program['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Feature this program</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Update Program</button>
                            <a href="programs.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>