<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../include/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>


<?php
if (get_setting('maintenance_mode') === '1') {
    header('HTTP/1.1 503 Service Unavailable');
    die('<h1>Maintenance Mode</h1><p>' . htmlspecialchars(get_setting('maintenance_message', 'We are currently performing maintenance. Please check back later.')) . '</p>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars(get_setting('meta_description', 'Default website description')) ?>">
  <meta name="keywords" content="<?= htmlspecialchars(get_setting('meta_keywords', 'default,keywords')) ?>">
  <title><?= htmlspecialchars(get_setting('site_title', 'My Website')) ?> - <?= htmlspecialchars($pageTitle ?? '') ?></title>

  <!-- Bootstrap CSS -->
  <link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* Sidebar styles */
    .sidebar {
      position: fixed;
      top: 0;
      right: -250px;
      height: 100%;
      width: 250px;
      background-color: #f8f9fa;
      box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
      transition: right 0.3s ease;
      padding: 2rem 1rem;
      z-index: 1050;
    }

    .sidebar.active {
      right: 0;
    }

    .sidebar a {
      display: block;
      margin: 1rem 0;
      color: #333;
      text-decoration: none;
      font-size: 1.1rem;
    }

    .sidebar .btn-close {
      position: absolute;
      top: 1rem;
      right: 1rem;
    }

    .navbar-toggler {
      border: none;
      background: transparent;
    }

    .nav-link {
      margin-right: 1rem;
    }

    /* Overlay when sidebar is active */
    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1040;
    }

    .overlay.active {
      display: block;
    }

    .navbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }


    .navbar-brand img {
      height: 40px; /* Set a fixed height for logo */
    }

    .hero-section {
      position: relative;
      overflow: hidden;
      background: linear-gradient(90deg, #feb21e 0%, #27a263 70%, #27a263 100%);
    }
    
    /* Optional: Add a subtle pattern overlay */
    .hero-section::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 10px 10px;
      z-index: 0;
    }
    
    .hero-section .container {
      position: relative;
      z-index: 1;
    }
    
    /* Button hover effect */
    .hero-section .btn-light:hover {
      background-color: rgba(255,255,255,0.9);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .hero-section .btn-light {
      transition: all 0.3s ease;
    }

    @media (max-width: 991.98px) {
      .desktop-menu {
        display: none;
      }
    }

    @media (min-width: 992px) {
      .mobile-menu-toggle {
        display: none;
      }
    }
  </style>
</head>
<body>
  
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
    <a class="navbar-brand" href="index.php">
      <img src="assets/images/logo.png" alt="Company Logo" class="img-fluid" width="70px">
      <span>Home</span>
    </a>

    <!-- Mobile hamburger toggle -->
    <button class="navbar-toggler mobile-menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Desktop menu -->
    <div class="collapse navbar-collapse justify-content-end desktop-menu">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="programs.php">Programs</a></li>
        <li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown ms-2">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= htmlspecialchars($_SESSION['first_name'] ?? 'Account') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item ms-2">
            <a class="btn btn-outline-success bg-success text-white" href="login.php">Sign In</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Overlay -->
  <div class="overlay" id="overlay"></div>

  <!-- Sidebar (Mobile Menu) -->
  <div class="sidebar" id="sidebar">
    <button class="btn-close" id="closeSidebar" aria-label="Close menu"></button>
    <a href="programs.php">Programs</a>
    <a href="blog.php">Blog</a>
    <a href="aboutus.php">About Us</a>
    <?php if ($isLoggedIn): ?>
      <a href="dashboard.php" class="btn btn-outline-primary mt-3">Dashboard</a>
      <a href="logout.php" class="btn btn-outline-danger mt-2">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-outline-primary mt-3">Sign In</a>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    const closeButton = document.getElementById('closeSidebar');

    function toggleMenu() {
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    }

    toggleButton.addEventListener('click', toggleMenu);
    closeButton.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
  </script>
</body>
</html>