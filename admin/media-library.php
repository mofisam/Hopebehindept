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
// Configuration
$uploadDir = '../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    $errors = [];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error: " . $file['error'];
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowedTypes);
    } elseif ($file['size'] > $maxFileSize) {
        $errors[] = "File too large. Maximum size: " . ($maxFileSize / 1024 / 1024) . "MB";
    }

    if (empty($errors)) {
        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['success_message'] = "File uploaded successfully!";
            header("Location: media-library.php");
            exit();
        } else {
            $errors[] = "Failed to move uploaded file";
        }
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $filename = basename($_POST['filename']);
    $filepath = $uploadDir . $filename;

    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            $_SESSION['success_message'] = "File deleted successfully!";
            header("Location: media-library.php");
            exit();
        } else {
            $errors[] = "Failed to delete file";
        }
    } else {
        $errors[] = "File not found";
    }
}

// Get list of media files
$mediaFiles = [];
if (file_exists($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filepath = $uploadDir . $file;
            $mediaFiles[] = [
                'name' => $file,
                'path' => $filepath,
                'size' => filesize($filepath),
                'type' => mime_content_type($filepath),
                'uploaded' => filemtime($filepath)
            ];
        }
    }
}

// Sort by upload date (newest first)
usort($mediaFiles, function($a, $b) {
    return $b['uploaded'] - $a['uploaded'];
});

// Set page title
$pageTitle = "Media Library";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Media Library</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-upload"></i> Upload Media
                </button>
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

            <div class="card">
                <div class="card-body">
                    <?php if (empty($mediaFiles)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder-x fs-1 text-muted"></i>
                            <h5 class="mt-3">No media files found</h5>
                            <p class="text-muted">Upload your first file using the button above</p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                            <?php foreach ($mediaFiles as $file): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <?php if (strpos($file['type'], 'image/') === 0): ?>
                                            <img src="<?= str_replace('../', '/', $file['path']) ?>" 
                                                 class="card-img-top" 
                                                 alt="<?= htmlspecialchars($file['name']) ?>"
                                                 style="height: 180px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 180px;">
                                                <i class="bi bi-file-earmark fs-1 text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate"><?= htmlspecialchars($file['name']) ?></h6>
                                            <p class="card-text small text-muted mb-1">
                                                <?= round($file['size'] / 1024, 1) ?> KB
                                            </p>
                                            <p class="card-text small text-muted">
                                                <?= date('M j, Y H:i', $file['uploaded']) ?>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="<?= str_replace('../', '/', $file['path']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <button class="btn btn-sm btn-outline-secondary copy-link"
                                                        data-path="<?= str_replace('../', '/', $file['path']) ?>">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="filename" value="<?= htmlspecialchars($file['name']) ?>">
                                                    <button type="submit" name="delete_file" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to delete this file?');">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="media_file" class="form-label">Select File</label>
                        <input class="form-control" type="file" id="media_file" name="media_file" required>
                        <div class="form-text">
                            Allowed types: JPEG, PNG, GIF, WEBP, PDF. Max size: 10MB.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Copy file path to clipboard
    document.querySelectorAll('.copy-link').forEach(button => {
        button.addEventListener('click', function() {
            const path = this.getAttribute('data-path');
            navigator.clipboard.writeText(path).then(() => {
                // Change icon temporarily to indicate success
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'bi bi-check';
                
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            });
        });
    });

    // Auto-focus file input when modal is shown
    document.getElementById('uploadModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('media_file').focus();
    });
</script>