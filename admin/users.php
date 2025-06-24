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
    if (isset($_POST['add_user'])) {
        // Add new user
        $email = trim($_POST['email']);
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $displayName = trim($_POST['display_name']);
        $roleId = (int)$_POST['role_id'];
        $password = $_POST['password'];
        $passwordConfirm = $_POST['password_confirm'];
        
        // Validate
        $errors = [];
        if (empty($email)) $errors[] = "Email is required";
        if (empty($firstName)) $errors[] = "First name is required";
        if (empty($lastName)) $errors[] = "Last name is required";
        if (empty($password)) $errors[] = "Password is required";
        if ($password !== $passwordConfirm) $errors[] = "Passwords do not match";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        
        if (empty($errors)) {
            // Check if email exists
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Email is already registered";
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users 
                    (email, password_hash, first_name, last_name, display_name, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $email, $passwordHash, $firstName, $lastName, $displayName, $roleId);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User added successfully!";
                    header("Location: users.php");
                    exit();
                } else {
                    $errors[] = "Error adding user: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['update_user'])) {
        // Update existing user
        $userId = (int)$_POST['user_id'];
        $email = trim($_POST['email']);
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $displayName = trim($_POST['display_name']);
        $roleId = (int)$_POST['role_id'];
        $password = $_POST['password'];
        $passwordConfirm = $_POST['password_confirm'];
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
        
        // Validate
        $errors = [];
        if (empty($email)) $errors[] = "Email is required";
        if (empty($firstName)) $errors[] = "First name is required";
        if (empty($lastName)) $errors[] = "Last name is required";
        if (!empty($password) && $password !== $passwordConfirm) $errors[] = "Passwords do not match";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        
        if (empty($errors)) {
            // Check if email exists for another user
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check->bind_param("si", $email, $userId);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $errors[] = "Email is already registered to another user";
            } else {
                // Prepare base query
                $query = "UPDATE users SET 
                    email = ?, first_name = ?, last_name = ?, 
                    display_name = ?, role_id = ?, is_verified = ?";
                $params = [$email, $firstName, $lastName, $displayName, $roleId, $isVerified];
                $types = "ssssii";
                
                // Add password update if provided
                if (!empty($password)) {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $query .= ", password_hash = ?";
                    $params[] = $passwordHash;
                    $types .= "s";
                }
                
                $query .= " WHERE user_id = ?";
                $params[] = $userId;
                $types .= "i";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User updated successfully!";
                    header("Location: users.php");
                    exit();
                } else {
                    $errors[] = "Error updating user: " . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        // Delete user (soft delete)
        $userId = (int)$_POST['user_id'];
        
        // Prevent deleting own account
        if ($userId == $_SESSION['user_id']) {
            $errors[] = "You cannot delete your own account";
        } else {
            $stmt = $conn->prepare("UPDATE users SET is_verified = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User deactivated successfully!";
                header("Location: users.php");
                exit();
            } else {
                $errors[] = "Error deactivating user: " . $conn->error;
            }
        }
    } elseif (isset($_POST['activate_user'])) {
        // Activate user
        $userId = (int)$_POST['user_id'];
        
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User activated successfully!";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Error activating user: " . $conn->error;
        }
    }
}

// Get all users with their roles
$users = [];
$query = "SELECT u.*, r.role_name 
          FROM users u
          JOIN user_roles r ON u.role_id = r.role_id
          ORDER BY u.last_name, u.first_name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get all roles for dropdown
$roles = [];
$roleQuery = "SELECT * FROM user_roles ORDER BY role_name";
$roleResult = $conn->query($roleQuery);
while ($row = $roleResult->fetch_assoc()) {
    $roles[] = $row;
}

// Set page title
$pageTitle = "Manage Users";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Users Management</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Add User
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
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                <?php if (!empty($user['display_name'])): ?>
                                                    <div class="text-muted small">@<?= htmlspecialchars($user['display_name']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['role_name']) ?></td>
                                            <td>
                                                <?php if ($user['is_verified']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $user['last_login'] ? date('M j, Y g:i a', strtotime($user['last_login'])) : 'Never' ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-user" 
                                                        data-id="<?= $user['user_id'] ?>"
                                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                                        data-first_name="<?= htmlspecialchars($user['first_name']) ?>"
                                                        data-last_name="<?= htmlspecialchars($user['last_name']) ?>"
                                                        data-display_name="<?= htmlspecialchars($user['display_name']) ?>"
                                                        data-role_id="<?= $user['role_id'] ?>"
                                                        data-is_verified="<?= $user['is_verified'] ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <?php if ($user['is_verified']): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-person-x"></i> Deactivate
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <button type="submit" name="activate_user" class="btn btn-sm btn-outline-success">
                                                            <i class="bi bi-person-check"></i> Activate
                                                        </button>
                                                    </form>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="display_name" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="display_name" name="display_name">
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Role *</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_display_name" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="edit_display_name" name="display_name">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_role_id" class="form-label">Role *</label>
                            <select class="form-select" id="edit_role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="edit_password_confirm" name="password_confirm">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_verified" name="is_verified">
                                <label class="form-check-label" for="edit_is_verified">
                                    Verified Account
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Handle edit button clicks
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            
            document.getElementById('edit_user_id').value = this.getAttribute('data-id');
            document.getElementById('edit_first_name').value = this.getAttribute('data-first_name');
            document.getElementById('edit_last_name').value = this.getAttribute('data-last_name');
            document.getElementById('edit_display_name').value = this.getAttribute('data-display_name');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_role_id').value = this.getAttribute('data-role_id');
            document.getElementById('edit_is_verified').checked = this.getAttribute('data-is_verified') === '1';
            
            modal.show();
        });
    });

    // Auto-focus first name field when add modal is shown
    document.getElementById('addUserModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('first_name').focus();
    });
</script>