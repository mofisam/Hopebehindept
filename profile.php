<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get current user data
$user_id = $_SESSION['user_id'];
$user = [];
$errors = [];
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $display_name = trim($_POST['display_name']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);

    // Basic validation
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already in use by another account";
    }
    $stmt->close();

    // Handle avatar upload
    $avatar = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and WEBP images are allowed";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "Image must be less than 2MB";
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('avatar_') . '.' . $ext;
            $upload_path = 'uploads/avatars/' . $new_filename;

            // Create directory if it doesn't exist
            if (!file_exists('uploads/avatars')) {
                mkdir('uploads/avatars', 0755, true);
            }

            // Delete old avatar if it exists
            if (!empty($avatar) && $avatar !== 'default-avatar.jpg') {
                $old_avatar = 'uploads/avatars/' . $avatar;
                if (file_exists($old_avatar)) {
                    unlink($old_avatar);
                }
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $avatar = $new_filename;
            } else {
                $errors[] = "Failed to upload avatar";
            }
        }
    }

    // Update profile if no errors
    if (empty($errors)) {
        $query = "UPDATE users SET 
                 first_name = ?, last_name = ?, display_name = ?, 
                 email = ?, bio = ?, avatar = ?
                 WHERE user_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $first_name, $last_name, $display_name, $email, $bio, $avatar, $user_id);

        if ($stmt->execute()) {
            // Update session data
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['display_name'] = $display_name;
            $_SESSION['email'] = $email;
            $_SESSION['avatar'] = $avatar;

            $success = "Profile updated successfully!";
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
        }
    }
}

// Set page title
$pageTitle = "My Profile";

// Include header
include 'include/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="cover-photo"></div>
        <div class="profile-actions">
            <a href="security.php" class="btn btn-outline-light">
                <i class="bi bi-shield-lock"></i> Security Settings
            </a>
        </div>
    </div>

    <div class="profile-body">
        <div class="profile-sidebar">
            <div class="avatar-upload">
                <div class="avatar-preview">
                    <img src="uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.jpg') ?>" 
                         alt="Profile picture" 
                         id="avatar-preview">
                    <label for="avatar-upload" class="avatar-edit">
                        <i class="bi bi-camera"></i>
                        <input type="file" id="avatar-upload" name="avatar" accept="image/*" form="profile-form">
                    </label>
                </div>
            </div>

            <div class="profile-info">
                <h3><?= htmlspecialchars($user['display_name'] ?? $user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <p class="member-since">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>

        <div class="profile-content">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="profile-form" class="profile-form">
                <div class="section-title">
                    <h4><i class="bi bi-person"></i> Personal Information</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            <label for="first_name">First Name *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            <label for="last_name">Last Name *</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mt-3">
                    <input type="text" class="form-control" id="display_name" name="display_name" 
                           value="<?= htmlspecialchars($user['display_name']) ?>">
                    <label for="display_name">Display Name</label>
                    <div class="form-text">This name will be visible to others</div>
                </div>

                <div class="form-floating mt-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                    <label for="email">Email *</label>
                </div>

                <div class="mt-3">
                    <label for="bio" class="form-label">About Me</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-header {
    position: relative;
    height: 200px;
}

.cover-photo {
    height: 100%;
    background: linear-gradient(360deg, #27a263 0%,rgb(54, 176, 113) 60%,rgb(46, 188, 115) 100%);
}

.profile-actions {
    position: absolute;
    top: 20px;
    right: 20px;
}

.profile-body {
    display: flex;
    padding: 0 30px 30px;
    position: relative;
}

.profile-sidebar {
    width: 300px;
    margin-top: -50px;
    padding-right: 30px;
    position: relative;
    z-index: 1;
}

.avatar-upload {
    margin-bottom: 20px;
}

.avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #fff;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

#avatar-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #fff;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

.avatar-edit i {
    font-size: 18px;
    color: #555;
}

#avatar-upload {
    display: none;
}

.profile-info h3 {
    font-weight: 600;
    margin-bottom: 5px;
}

.member-since {
    color: #6c757d;
    font-size: 0.9rem;
}

.profile-content {
    flex: 1;
    padding-top: 30px;
}

.section-title {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.section-title h4 {
    font-weight: 600;
    margin: 0;
    font-size: 1.2rem;
}

.section-title i {
    margin-right: 10px;
    color: #6e8efb;
}

.profile-form {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .profile-body {
        flex-direction: column;
        padding: 0 15px 20px;
    }
    
    .profile-sidebar {
        width: 100%;
        margin-top: -30px;
        padding-right: 0;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .profile-content {
        padding-top: 20px;
    }
}
</style>

<script>
// Preview avatar before upload
document.getElementById('avatar-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('avatar-preview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'include/footer.php'; ?>