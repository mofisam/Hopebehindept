<?php
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
              <h2 class="h4 mb-0" style="color: #ffb420;">Welcome Back</h2>
              <p class="text-muted">Sign in to access your account</p>
            </div>

            <!-- Login Form -->
            <form>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" placeholder="your@email.com" required>
                </div>
              </div>
              
              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-lock text-muted"></i>
                  </span>
                  <input type="password" class="form-control border-start-0" id="password" placeholder="••••••••" required>
                  <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                  </div>
                  <a href="forget_password.php" class="small text-primary">Forgot password?</a>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="background-color: #ffb420; border-color: #ffb420;">
                Sign In
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Don't have an account? <a href="signup.php" class="text-primary">Create one</a></p>
                <div class="separator my-3">or continue with</div>
                <!--
                <div class="d-flex justify-content-center gap-3">
                  <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                    <i class="fab fa-google"></i>
                  </a>
                  <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                  <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                    <i class="fab fa-twitter"></i>
                  </a>
                </div>
                -->
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
  
  .separator {
    display: flex;
    align-items: center;
    text-align: center;
    color: #6c757d;
  }
  
  .separator::before,
  .separator::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
  }
  
  .separator::before {
    margin-right: .75rem;
  }
  
  .separator::after {
    margin-left: .75rem;
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