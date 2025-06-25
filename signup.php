<?php
// Start session and include header
session_start();
// Include database configuration
require_once "/config/db.php";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (!isset($_POST['terms'])) {
        $errors[] = "You must agree to the terms";
    }
    
    // Check if email exists
    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    
    if ($checkEmail->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    $checkEmail->close();
    
    // If no errors, create user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $role_id = 2; // Default user role
        
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, first_name, last_name, role_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $email, $passwordHash, $firstName, $lastName, $role_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    }
}
include 'include/header.php';
?>

<!-- Sign Up Section -->
<section class="signup-section py-5" style="background-color: #f8f9fa; min-height: 80vh;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <!-- Sign Up Card -->
        <div class="card border-0 shadow-lg">
          <div class="card-body p-5">
            <!-- Logo Space -->
            <div class="text-center mb-4">
              <a class="navbar-brand d-inline-block mb-3" href="#">
                <img src="assets/images/logo.jpg" alt="Company Logo">
              </a>
              <h2 class="h4 mb-0 text-success">Create Account</h2>
              <p class="text-muted">Join our community today</p>
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

            <!-- Sign Up Form -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="firstName" class="form-label">First Name</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="firstName" name="firstName" placeholder="John" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="lastName" class="form-label">Last Name</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="lastName" name="lastName" placeholder="Doe" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>
              
              <div class="mb-3">
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
                <small class="text-muted">Minimum 8 characters</small>
              </div>
              
              <div class="mb-4">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-lock text-muted"></i>
                  </span>
                  <input type="password" class="form-control border-start-0" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                </div>
              </div>
              
              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                <label class="form-check-label small" for="terms">
                  I agree to the <a href="#" class="text-success">Terms of Service</a> and <a href="#" class="text-success">Privacy Policy</a>
                </label>
              </div>
              
              <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                Create Account
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Already have an account? <a href="login.php" class="text-success">Sign in</a></p>
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
  .signup-section {
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
  
  .form-check-input:checked {
    background-color: #28a745; /* Green color for checked checkbox */
    border-color: #28a745;
  }
  
  .text-success {
    color: #28a745 !important; /* Green color for links */
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