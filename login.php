<?php

session_start();

// Database connection
$db = new mysqli('localhost', 'root', '1234', 'Hopebehindebt');

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $db->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate inputs
    $errors = [];
    
    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required";
    }

    if (empty($errors)) {
        // Check user credentials
        $stmt = $db->prepare("SELECT user_id, email, password_hash, first_name, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role_id'] = $user['role_id'];
                
                // Remember me functionality
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 86400 * 30, "/"); // 30 days
                    
                    // Store token in database
                    $updateStmt = $db->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                    $updateStmt->bind_param("si", $token, $user['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                // Update last login
                $updateLogin = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $updateLogin->bind_param("i", $user['user_id']);
                $updateLogin->execute();
                $updateLogin->close();
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                $stmt->close();
                $db->close();
                ob_end_flush();
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
        $stmt->close();
    }
}

include 'include/header.php';
?>

<!-- Login Section -->
<section class="login-section py-5" style="background-color: #f8f9fa; min-height: 80vh;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <!-- Login Card -->
        <div class="card border-0 shadow-lg">
          <div class="card-body p-5">
            <!-- Logo Space -->
            <div class="text-center mb-4">
              <a class="navbar-brand d-inline-block mb-3" href="#">
                <img src="assets/images/logo.jpg" alt="Company Logo" width="180">
              </a>
              <h2 class="h4 mb-0 text-success">Welcome Back</h2>
              <p class="text-muted">Sign in to access your account</p>
            </div>

            <!-- Display errors if any -->
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>
              
              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-lock text-muted"></i>
                  </span>
                  <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
                  <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label class="form-check-label small" for="remember">Remember me</label>
                  </div>
                  <a href="forget_password.php" class="small text-success">Forgot password?</a>
                </div>
              </div>
              
              <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                Sign In
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Don't have an account? <a href="signup.php" class="text-success">Create one</a></p>
              </div>
            </form>
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
  .login-section {
    display: flex;
    align-items: center;
  }
  
  .toggle-password {
    cursor: pointer;
  }
  
  .toggle-password:hover {
    background-color: #f8f9fa;
  }
  
  .btn-outline-secondary {
    border-color: #dee2e6;
  }
  
  .btn-outline-secondary:hover {
    background-color: #f8f9fa;
  }
  
  .text-success {
    color: #28a745 !important;
  }
  
  .btn-success {
    background-color: #28a745;
    border-color: #28a745;
  }
  
  .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
  }
</style>

<!-- JavaScript for Password Toggle -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('.toggle-password');
    const password = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });
  });
</script>