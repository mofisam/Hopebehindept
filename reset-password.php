<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/db.php';

// Initialize variables
$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;

// Validate token if present
if (!empty($token)) {
    $stmt = $mysqli->prepare("SELECT user_id, reset_token_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $expires = strtotime($user['reset_token_expires']);
        
        if (time() < $expires) {
            $valid_token = true;
            $_SESSION['reset_user_id'] = $user['user_id'];
            $_SESSION['reset_token'] = $token;
        } else {
            $errors[] = "Password reset link has expired";
        }
    } else {
        $errors[] = "Invalid password reset link";
    }
    $stmt->close();
}

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'])) {
    // Validate CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission";
    } else {
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($new_password) || empty($confirm_password)) {
            $errors[] = "Both password fields are required";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors) && isset($_SESSION['reset_user_id'], $_SESSION['reset_token'])) {
            // Verify token again
            $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ? AND reset_token = ?");
            $stmt->bind_param("is", $_SESSION['reset_user_id'], $_SESSION['reset_token']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Update password
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $updateStmt = $mysqli->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?");
                $updateStmt->bind_param("si", $password_hash, $_SESSION['reset_user_id']);
                
                if ($updateStmt->execute()) {
                    $success = "Password updated successfully. You can now <a href='login.php' class='text-success'>login</a>.";
                    // Clear reset session data
                    unset($_SESSION['reset_user_id'], $_SESSION['reset_token']);
                } else {
                    $errors[] = "Failed to update password. Please try again.";
                }
                $updateStmt->close();
            } else {
                $errors[] = "Invalid password reset session";
            }
            $stmt->close();
        } else {
            $errors[] = "Invalid password reset request";
        }
    }
}

// Generate new CSRF token for the form
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

include 'include/header.php';
?>

<!-- Reset Password Section -->
<section class="reset-password-section py-5" style="background-color: #f8f9fa; min-height: 80vh;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <!-- Reset Password Card -->
        <div class="card border-0 shadow-lg">
          <div class="card-body p-5">
            <!-- Logo Space -->
            <div class="text-center mb-4">
              <a class="navbar-brand d-inline-block mb-3" href="#">
                <img src="assets/images/logo.jpg" alt="Company Logo" width="180">
              </a>
              <h2 class="h4 mb-0 text-success">Reset Password</h2>
              <p class="text-muted">Enter your new password below</p>
            </div>

            <!-- Display messages -->
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
              <div class="alert alert-success">
                <?php echo $success; ?>
              </div>
            <?php endif; ?>

            <?php if ($valid_token || !empty($success)): ?>
              <!-- Reset Password Form -->
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . (!empty($token) ? '?token=' . htmlspecialchars($token) : ''); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                  <label for="password" class="form-label">New Password</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                      <i class="far fa-eye"></i>
                    </button>
                  </div>
                  <small class="text-muted">Minimum 8 characters</small>
                </div>
                
                <div class="mb-4">
                  <label for="confirm_password" class="form-label">Confirm New Password</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                  </div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                  Update Password
                </button>
              </form>
            <?php elseif (empty($token)): ?>
              <div class="alert alert-info">
                Please use the password reset link sent to your email.
              </div>
            <?php endif; ?>
            
            <div class="text-center small mt-4">
              <p class="text-muted">Remember your password? <a href="login.php" class="text-success">Sign in</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
include 'include/footer.php';
?>

<!-- Additional Styles -->
<style>
  .reset-password-section {
    display: flex;
    align-items: center;
  }
  
  .card {
    border-radius: 10px;
  }
  
  .btn-success {
    background-color: #28a745;
    border-color: #28a745;
  }
  
  .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
  }
  
  .text-success {
    color: #28a745 !important;
  }
  
  .toggle-password {
    cursor: pointer;
  }
  
  .toggle-password:hover {
    background-color: #f8f9fa;
  }
</style>

<!-- JavaScript for Password Toggle -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('.toggle-password');
    const password = document.querySelector('#password');
    
    if (togglePassword && password) {
      togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye-slash');
      });
    }
  });
</script>