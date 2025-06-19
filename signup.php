<?php
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
                <img src="assets/images/logo.jpg" alt="Company Logo" width="180">
              </a>
              <h2 class="h4 mb-0" style="color: #ffb420;">Create Account</h2>
              <p class="text-muted">Join our community today</p>
            </div>

            <!-- Sign Up Form -->
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="firstName" class="form-label">First Name</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="firstName" placeholder="John" required>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="lastName" class="form-label">Last Name</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="lastName" placeholder="Doe" required>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" placeholder="your@email.com" required>
                </div>
              </div>
              
              <div class="mb-3">
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
                <small class="text-muted">Minimum 8 characters</small>
              </div>
              
              <div class="mb-4">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-lock text-muted"></i>
                  </span>
                  <input type="password" class="form-control border-start-0" id="confirmPassword" placeholder="••••••••" required>
                </div>
              </div>
              
              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small" for="terms">
                  I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                </label>
              </div>
              
              <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="background-color: #ffb420; border-color: #ffb420;">
                Create Account
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Already have an account? <a href="login.php" class="text-primary">Sign in</a></p>
                <div class="separator my-3">or sign up with</div>
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
  .signup-section {
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
  
  .form-check-input:checked {
    background-color: #ffb420;
    border-color: #ffb420;
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