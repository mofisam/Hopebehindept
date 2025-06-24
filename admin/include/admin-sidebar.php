<?php
// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}
?>

<!-- Admin Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <!-- Blog Management Section -->
            <li class="nav-item">
                <a class="nav-link text-white" href="blog-posts.php">
                    <i class="bi bi-newspaper me-2"></i>
                    Blog Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="blog-categories.php">
                    <i class="bi bi-bookmarks me-2"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="blog-tags.php">
                    <i class="bi bi-tags me-2"></i>
                    Tags
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="blog-comments.php">
                    <i class="bi bi-chat-left-text me-2"></i>
                    Comments
                </a>
            </li>
            
            <!-- Program Management Section -->
            <li class="nav-item">
                <a class="nav-link text-white" href="programs.php">
                    <i class="bi bi-collection me-2"></i>
                    Programs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="program-categories.php">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Program Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="success-stories.php">
                    <i class="bi bi-star me-2"></i>
                    Success Stories
                </a>
            </li>
            
            <!-- User Management Section -->
            <li class="nav-item">
                <a class="nav-link text-white" href="users.php">
                    <i class="bi bi-people me-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="user-roles.php">
                    <i class="bi bi-shield-lock me-2"></i>
                    User Roles
                </a>
            </li>
            
            <!-- Media & Settings -->
            <li class="nav-item">
                <a class="nav-link text-white" href="media-library.php">
                    <i class="bi bi-images me-2"></i>
                    Media Library
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="settings.php">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>

        <hr class="border-secondary my-4">

        <!-- Quick Actions -->
        <div class="px-3 mb-4">
            <h6 class="text-uppercase text-white-50 mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <a href="blog-post-add.php" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> New Blog Post
                </a>
                <a href="program-add.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Program
                </a>
            </div>
        </div>

        <!-- Current User -->
        <div class="px-3 py-2 border-top border-secondary">
            <div class="d-flex align-items-center">
                <img src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.jpg') ?>" 
                     class="rounded-circle me-2" width="32" height="32" alt="User avatar">
                <div>
                    <div class="text-white small"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></div>
                    <small class="text-white-50"><?= htmlspecialchars($_SESSION['user_role']) ?></small>
                </div>
            </div>
            <div class="mt-2">
                <a href="../profile.php" class="btn btn-outline-light btn-sm me-1">
                    <i class="bi bi-person"></i>
                </a>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .sidebar {
        min-height: 100vh;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .nav-link {
        border-radius: 4px;
        margin-bottom: 2px;
        transition: all 0.2s;
    }
    
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .nav-link.active {
        background-color: rgba(255, 180, 32, 0.2);
        color: #ffb420 !important;
        font-weight: 500;
    }
    
    .nav-link i {
        width: 20px;
        text-align: center;
    }
</style>

<script>
    // Highlight current page in sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (currentPage === linkPage) {
                link.classList.add('active');
            }
            
            // Handle cases where the URL might have query parameters
            if (linkPage !== 'dashboard.php' && currentPage.includes(linkPage.replace('.php', ''))) {
                link.classList.add('active');
            }
        });
    });
</script>