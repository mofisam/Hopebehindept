<?php
session_start();
require_once 'config/db.php';
require_once __DIR__ . '/include/functions.php';
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$email = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check user credentials
        $query = "SELECT user_id, email, password_hash, first_name, last_name, role_id, is_verified 
                  FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Check if account is verified
                if (!$user['is_verified']) {
                    $error = 'Please verify your email before logging in';
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_role'] = $user['role_id'] == 1 ? 'admin' : 'user';
                    
                    // Update last login
                    $updateQuery = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("i", $user['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Redirect to appropriate page
                    if ($_SESSION['user_role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Financial Freedom</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-section {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            max-width: 180px;
        }
        .btn-login {
            background-color: #ffb420;
            color: white;
        }
        .btn-login:hover {
            background-color: #e6a21d;
        }
        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6c757d;
            margin: 1.5rem 0;
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
        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <section class="login-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <!-- Login Card -->
                    <div class="card login-card border-0 shadow-lg">
                        <div class="card-body p-5 pt-0">
                            <!-- Logo -->
                            <div class="text-center mb-4">
                                <a href="index.php">
                                    <img src="assets/images/logo.jpg" alt="Financial Freedom Logo" class="login-logo">
                                </a>
                                <p class="h3 text-muted">
                                    <?= htmlspecialchars(get_setting('site_title', 'My Website')) ?>
                                </p>
                                <h2 class="h4 mb-0 text-success" >Welcome Back</h2>
                                <p class="text-muted">Sign in to your account</p>
                            </div>

                            <!-- Error Message -->
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <!-- Login Form -->
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email" class="form-control border-start-0" id="email" name="email" 
                                               value="<?= htmlspecialchars($email) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                    <a href="forgot-password.php" class="text-primary">Forgot password?</a>
                                </div>
                                
                                <button type="submit" class="btn btn-login w-100 py-2 mb-3 bg-success">Sign In</button>
                                
                                <div class="text-center small mt-4">
                                    <p class="text-muted">Don't have an account? <a href="signup.php" class="text-primary">Create one</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Password Toggle Script -->
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
</body>
</html>