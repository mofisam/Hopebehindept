<?php
include 'include/header.php';
?>
<!-- Forgot Password Section -->
<section class="forgot-password-section py-5" style="background-color: #f8f9fa; min-height: 80vh;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <!-- Forgot Password Card -->
        <div class="card border-0 shadow-lg">
          <div class="card-body p-5">
            <!-- Logo Space -->
            <div class="text-center mb-4">
              <a class="navbar-brand d-inline-block mb-3" href="#">
                <img src="assets/images/logo.jpg" alt="Company Logo" width="180">
              </a>
              <h2 class="h4 mb-0" style="color: #ffb420;">Reset Your Password</h2>
              <p class="text-muted">Enter your email to receive a password reset link</p>
            </div>

            <!-- Forgot Password Form -->
            <form>
              <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" placeholder="your@email.com" required>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="background-color: #ffb420; border-color: #ffb420;">
                Send Reset Link
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Remember your password? <a href="login.php" class="text-primary">Sign in</a></p>
                <p class="text-muted">Don't have an account? <a href="signup.php" class="text-primary">Create one</a></p>
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
  .forgot-password-section {
    display: flex;
    align-items: center;
  }
  
  .card {
    border-radius: 10px;
  }
  
  .btn-primary:hover {
    background-color: #e6a21d !important;
    border-color: #e6a21d !important;
  }
</style>