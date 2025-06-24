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
    if (isset($_POST['add_role'])) {
        // Add new role
        $roleName = trim($_POST['role_name']);
        $description = trim($_POST['description']);
        
        // Validate
        $errors = [];
        if (empty($roleName)) $errors[] = "Role name is required";
        
        if (empty($errors)) {
            // Check if role exists
            $check = $conn->prepare("SELECT role_id FROM user_roles WHERE role_name = ?");
            $check->bind_param("s", $roleName);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Role name already exists";
            } else {
                $stmt = $conn->prepare("INSERT INTO user_roles (role_name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $roleName, $description);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Role added successfully!";
                    header("Location: user-roles.php");
                    exit();
                } else {
                    $errors[] = "Error adding role: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['update_role'])) {
        // Update existing role
        $roleId = (int)$_POST['role_id'];
        $roleName = trim($_POST['role_name']);
        $description = trim($_POST['description']);
        
        // Validate
        $errors = [];
        if (empty($roleName)) $errors[] = "Role name is required";
        
        if (empty($errors)) {
            // Check if role exists (excluding current role)
            $check = $conn->prepare("SELECT role_id FROM user_roles WHERE role_name = ? AND role_id != ?");
            $check->bind_param("si", $roleName, $roleId);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Role name already exists";
            } else {
                $stmt = $conn->prepare("UPDATE user_roles SET role_name = ?, description = ? WHERE role_id = ?");
                $stmt->bind_param("ssi", $roleName, $description, $roleId);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Role updated successfully!";
                    header("Location: user-roles.php");
                    exit();
                } else {
                    $errors[] = "Error updating role: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['delete_role'])) {
        // Delete role
        $roleId = (int)$_POST['role_id'];
        
        // Prevent deleting admin or default user role
        if ($roleId == 1 || $roleId == 2) {
            $errors[] = "Cannot delete system default roles";
        } else {
            // First check if role is in use
            $check = $conn->prepare("SELECT user_id FROM users WHERE role_id = ? LIMIT 1");
            $check->bind_param("i", $roleId);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Cannot delete role - it is assigned to one or more users";
            } else {
                $stmt = $conn->prepare("DELETE FROM user_roles WHERE role_id = ?");
                $stmt->bind_param("i", $roleId);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Role deleted successfully!";
                    header("Location: user-roles.php");
                    exit();
                } else {
                    $errors[] = "Error deleting role: " . $conn->error;
                }
            }
        }
    }
}

// Get all roles with user counts
$roles = [];
$query = "SELECT r.*, COUNT(u.user_id) as user_count 
          FROM user_roles r
          LEFT JOIN users u ON r.role_id = u.role_id
          GROUP BY r.role_id
          ORDER BY r.role_name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Set page title
$pageTitle = "Manage User Roles";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User Roles</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="bi bi-plus-circle"></i> Add Role
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
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Users</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($roles)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No roles found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($role['role_name']) ?></strong>
                                                <?php if ($role['role_id'] == 1): ?>
                                                    <span class="badge bg-primary ms-2">System</span>
                                                <?php elseif ($role['role_id'] == 2): ?>
                                                    <span class="badge bg-secondary ms-2">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($role['description'] ?? '—') ?></td>
                                            <td><?= $role['user_count'] ?></td>
                                            <td>
                                                <?php if ($role['role_id'] > 2): ?>
                                                    <button class="btn btn-sm btn-outline-primary edit-role" 
                                                            data-id="<?= $role['role_id'] ?>"
                                                            data-role_name="<?= htmlspecialchars($role['role_name']) ?>"
                                                            data-description="<?= htmlspecialchars($role['description']) ?>">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                        <input type="hidden" name="role_id" value="<?= $role['role_id'] ?>">
                                                        <button type="submit" name="delete_role" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">System role</span>
                                                <?php endif; ?>
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Role Name *</label>
                        <input type="text" class="form-control" id="role_name" name="role_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_role" class="btn btn-success">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" id="edit_role_id" name="role_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_role_name" class="form-label">Role Name *</label>
                        <input type="text" class="form-control" id="edit_role_name" name="role_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_role" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Handle edit button clicks
    document.querySelectorAll('.edit-role').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
            
            document.getElementById('edit_role_id').value = this.getAttribute('data-id');
            document.getElementById('edit_role_name').value = this.getAttribute('data-role_name');
            document.getElementById('edit_description').value = this.getAttribute('data-description');
            
            modal.show();
        });
    });

    // Auto-focus role name field when add modal is shown
    document.getElementById('addRoleModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('role_name').focus();
    });
</script>