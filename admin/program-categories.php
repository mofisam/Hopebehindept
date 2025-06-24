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
    if (isset($_POST['add_category'])) {
        // Add new program category
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description']);
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = "Category name is required";
        
        if (empty($errors)) {
            // Check if slug exists
            $check = $conn->prepare("SELECT category_id FROM program_categories WHERE slug = ?");
            $check->bind_param("s", $slug);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Slug is already in use";
            } else {
                $stmt = $conn->prepare("INSERT INTO program_categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $slug, $description);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Program category added successfully!";
                    header("Location: program-categories.php");
                    exit();
                } else {
                    $errors[] = "Error adding program category: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['update_category'])) {
        // Update existing category
        $category_id = (int)$_POST['category_id'];
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);
        $description = trim($_POST['description']);
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = "Category name is required";
        
        if (empty($errors)) {
            // Check if slug exists for another category
            $check = $conn->prepare("SELECT category_id FROM program_categories WHERE slug = ? AND category_id != ?");
            $check->bind_param("si", $slug, $category_id);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Slug is already in use by another category";
            } else {
                $stmt = $conn->prepare("UPDATE program_categories SET name = ?, slug = ?, description = ? WHERE category_id = ?");
                $stmt->bind_param("sssi", $name, $slug, $description, $category_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Program category updated successfully!";
                    header("Location: program-categories.php");
                    exit();
                } else {
                    $errors[] = "Error updating program category: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        // Delete category
        $category_id = (int)$_POST['category_id'];
        
        // First check if category is in use
        $check = $conn->prepare("SELECT program_id FROM program_category_map WHERE category_id = ? LIMIT 1");
        $check->bind_param("i", $category_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $errors[] = "Cannot delete category - it is assigned to one or more programs";
        } else {
            $stmt = $conn->prepare("DELETE FROM program_categories WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Program category deleted successfully!";
                header("Location: program-categories.php");
                exit();
            } else {
                $errors[] = "Error deleting program category: " . $conn->error;
            }
        }
    }
}

// Get all program categories with program counts
$categories = [];
$query = "SELECT c.*, COUNT(pc.program_id) as program_count 
          FROM program_categories c
          LEFT JOIN program_category_map pc ON c.category_id = pc.category_id
          GROUP BY c.category_id
          ORDER BY c.name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Set page title
$pageTitle = "Manage Program Categories";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Program Categories</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> Add Category
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
                                    <th>Programs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No program categories found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                <?php if (!empty($category['description'])): ?>
                                                    <div class="text-muted small"><?= htmlspecialchars($category['description']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($category['slug']) ?></td>
                                            <td><?= $category['program_count'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-category" 
                                                        data-id="<?= $category['category_id'] ?>"
                                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                                        data-slug="<?= htmlspecialchars($category['slug']) ?>"
                                                        data-description="<?= htmlspecialchars($category['description']) ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this program category?');">
                                                    <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Program Category</h5>
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
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Program Category</h5>
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
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
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
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            
            document.getElementById('edit_category_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_slug').value = this.getAttribute('data-slug');
            document.getElementById('edit_description').value = this.getAttribute('data-description');
            
            modal.show();
        });
    });

    // Auto-focus name field when add modal is shown
    document.getElementById('addCategoryModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('name').focus();
    });
</script>