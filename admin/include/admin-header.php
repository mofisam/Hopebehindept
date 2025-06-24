<?php
// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom Admin CSS -->
    <link href="../assets/css/admin.css" rel="stylesheet">
    
    <!-- DataTables CSS (for tables) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Summernote CSS (for rich text editors) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    
    <?php if (isset($customCSS)): ?>
        <!-- Page-specific CSS -->
        <link href="<?= $customCSS ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="bg-light">
    <!-- Header Navigation -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="dashboard.php">
            <img src="../assets/images/logo.png" alt="Site Logo" height="30" class="me-2">
            Admin Panel
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" 
                data-bs-toggle="collapse" data-bs-target="#sidebarMenu" 
                aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Search Bar -->
        <div class="w-100 d-none d-md-flex">
            <form class="w-100 mx-4" action="search.php" method="GET">
                <div class="input-group">
                    <input class="form-control form-control-dark" type="search" 
                           placeholder="Search..." aria-label="Search" name="q">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- User Dropdown -->
        <div class="dropdown px-3">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" 
               id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.jpg') ?>" 
                     alt="User avatar" width="32" height="32" class="rounded-circle me-2">
                <span class="d-none d-lg-inline"><?= htmlspecialchars($_SESSION['first_name']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                <li><a class="dropdown-item" href="../profile.php">
                    <i class="bi bi-person me-2"></i>Profile
                </a></li>
                <li><a class="dropdown-item" href="settings.php">
                    <i class="bi bi-gear me-2"></i>Settings
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Sign out
                </a></li>
            </ul>
        </div>
    </header>

    <!-- Notification Alert -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3 position-fixed top-0 end-0" 
             style="z-index: 1100; max-width: 400px;" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3 position-fixed top-0 end-0" 
             style="z-index: 1100; max-width: 400px;" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

   