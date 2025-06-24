<?php
session_start();
// Load configuration and PHPMailer
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/.env';
require_once __DIR__ . '/vendor/autoload.php'; // Assuming you've installed PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$errors = [];
$success = '';

// Process password reset request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission";
    } else {
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email address is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (empty($errors)) {
            // Check if email exists
            $stmt = $conn->prepare("SELECT user_id, first_name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate reset token (valid for 1 hour)
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                
                // Store token in database
                $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?");
                $updateStmt->bind_param("ssi", $token, $expires, $user['user_id']);
                
                if ($updateStmt->execute()) {
                    // Send reset email using SMTP
                    $mail = new PHPMailer(true);
                    
                    try {
                        // SMTP Configuration (from your config)
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USER;
                        $mail->Password = SMTP_PASS;
                        $mail->SMTPSecure = SMTP_ENCRYPTION;
                        $mail->Port = SMTP_PORT;
                        
                        // Recipients
                        $mail->setFrom('noreply@yourdomain.com', 'Hope Behind Debt');
                        $mail->addAddress($email, $user['first_name']);
                        
                        // Content
                        $resetLink = "http://hopebehindebt/reset-password.php?token=$token";
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body = "
                            <h2>Password Reset Request</h2>
                            <p>Hello {$user['first_name']},</p>
                            <p>We received a request to reset your password. Click the link below to proceed:</p>
                            <p><a href='$resetLink' style='color: #28a745;'>Reset My Password</a></p>
                            <p>This link will expire in 1 hour.</p>
                            <p>If you didn't request this, please ignore this email.</p>
                            <hr>
                            <p><small>Hope Behind Debt Team</small></p>
                        ";
                        $mail->AltBody = "Password Reset Link: $resetLink (expires in 1 hour)";
                        
                        $mail->send();
                        $success = "Password reset link has been sent to your email";
                    } catch (Exception $e) {
                        error_log("Mailer Error: " . $mail->ErrorInfo);
                        $errors[] = "Failed to send reset email. Please try again later.";
                    }
                } else {
                    $errors[] = "Failed to generate reset token";
                }
                $updateStmt->close();
            } else {
                // For security, don't reveal if email exists
                $success = "If this email exists in our system, you'll receive a reset link";
            }
            $stmt->close();
        }
    }
}

// Generate new CSRF token for the form
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

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
              <h2 class="h4 mb-0 text-success">Reset Your Password</h2>
              <p class="text-muted">Enter your email to receive a password reset link</p>
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
                <?php echo htmlspecialchars($success); ?>
              </div>
            <?php endif; ?>

            <!-- Forgot Password Form -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
              <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
              
              <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-envelope text-muted"></i>
                  </span>
                  <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>
              
              <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                Send Reset Link
              </button>
              
              <div class="text-center small mt-4">
                <p class="text-muted">Remember your password? <a href="login.php" class="text-success">Sign in</a></p>
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
  .forgot-password-section {
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
</style>