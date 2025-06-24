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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_tag'])) {
        // Add new tag
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = "Tag name is required";
        
        if (empty($errors)) {
            // Check if slug exists
            $check = $conn->prepare("SELECT tag_id FROM blog_tags WHERE slug = ?");
            $check->bind_param("s", $slug);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Slug is already in use";
            } else {
                $stmt = $conn->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $slug);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Tag added successfully!";
                    header("Location: blog-tags.php");
                    exit();
                } else {
                    $errors[] = "Error adding tag: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['update_tag'])) {
        // Update existing tag
        $tag_id = (int)$_POST['tag_id'];
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = "Tag name is required";
        
        if (empty($errors)) {
            // Check if slug exists for another tag
            $check = $conn->prepare("SELECT tag_id FROM blog_tags WHERE slug = ? AND tag_id != ?");
            $check->bind_param("si", $slug, $tag_id);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Slug is already in use by another tag";
            } else {
                $stmt = $conn->prepare("UPDATE blog_tags SET name = ?, slug = ? WHERE tag_id = ?");
                $stmt->bind_param("ssi", $name, $slug, $tag_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Tag updated successfully!";
                    header("Location: blog-tags.php");
                    exit();
                } else {
                    $errors[] = "Error updating tag: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['delete_tag'])) {
        // Delete tag
        $tag_id = (int)$_POST['tag_id'];
        
        // First check if tag is in use
        $check = $conn->prepare("SELECT post_id FROM blog_post_tags WHERE tag_id = ? LIMIT 1");
        $check->bind_param("i", $tag_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $errors[] = "Cannot delete tag - it is assigned to one or more posts";
        } else {
            $stmt = $conn->prepare("DELETE FROM blog_tags WHERE tag_id = ?");
            $stmt->bind_param("i", $tag_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Tag deleted successfully!";
                header("Location: blog-tags.php");
                exit();
            } else {
                $errors[] = "Error deleting tag: " . $conn->error;
            }
        }
    }
}

// Get all tags with post counts
$tags = [];
$query = "SELECT t.*, COUNT(pt.post_id) as post_count 
          FROM blog_tags t
          LEFT JOIN blog_post_tags pt ON t.tag_id = pt.tag_id
          GROUP BY t.tag_id
          ORDER BY t.name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

// Set page title
$pageTitle = "Manage Blog Tags";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Blog Tags</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTagModal">
                    <i class="bi bi-plus-circle"></i> Add Tag
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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Posts</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tags)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No tags found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tags as $tag): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tag['name']) ?></td>
                                            <td><?= htmlspecialchars($tag['slug']) ?></td>
                                            <td><?= $tag['post_count'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-tag" 
                                                        data-id="<?= $tag['tag_id'] ?>"
                                                        data-name="<?= htmlspecialchars($tag['name']) ?>"
                                                        data-slug="<?= htmlspecialchars($tag['slug']) ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                                                    <input type="hidden" name="tag_id" value="<?= $tag['tag_id'] ?>">
                                                    <button type="submit" name="delete_tag" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1" aria-labelledby="addTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTagModalLabel">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug">
                        <small class="text-muted">Leave blank to auto-generate from name</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_tag" class="btn btn-success">Add Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" id="edit_tag_id" name="tag_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="edit_slug" name="slug">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_tag" class="btn btn-primary">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value) {
            const slug = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove non-word chars
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/--+/g, '-')     // Replace multiple - with single -
                .trim();                  // Trim - from start and end
            slugField.value = slug;
        }
    });

    // Handle edit button clicks
    document.querySelectorAll('.edit-tag').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editTagModal'));
            
            document.getElementById('edit_tag_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_slug').value = this.getAttribute('data-slug');
            
            modal.show();
        });
    });

    // Auto-focus name field when add modal is shown
    document.getElementById('addTagModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('name').focus();
    });
</script>