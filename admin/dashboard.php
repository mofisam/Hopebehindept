<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats = [];

// Blog Statistics
$result = $conn->query("SELECT COUNT(*) as total_posts FROM blog_posts");
$stats['total_posts'] = $result->fetch_assoc()['total_posts'];

$result = $conn->query("SELECT COUNT(*) as published_posts FROM blog_posts WHERE status = 'published'");
$stats['published_posts'] = $result->fetch_assoc()['published_posts'];

$result = $conn->query("SELECT COUNT(*) as total_comments FROM blog_comments");
$stats['total_comments'] = $result->fetch_assoc()['total_comments'];

// Program Statistics
$result = $conn->query("SELECT COUNT(*) as total_programs FROM programs");
$stats['total_programs'] = $result->fetch_assoc()['total_programs'];

$result = $conn->query("SELECT SUM(amount_raised) as total_raised FROM programs");
$stats['total_raised'] = number_format($result->fetch_assoc()['total_raised'] ?? 0, 2);

// User Statistics
$result = $conn->query("SELECT COUNT(*) as total_users FROM users");
$stats['total_users'] = $result->fetch_assoc()['total_users'];

$result = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['new_users'] = $result->fetch_assoc()['new_users'];

// Recent Activities
$activities = [];
$query = "(
    SELECT 'post' as type, post_id as id, title as name, author_id, created_at 
    FROM blog_posts 
    ORDER BY created_at DESC 
    LIMIT 5
) UNION ALL (
    SELECT 'program' as type, program_id as id, title as name, NULL as author_id, created_at 
    FROM programs 
    ORDER BY created_at DESC 
    LIMIT 5
) UNION ALL (
    SELECT 'comment' as type, comment_id as id, LEFT(content, 50) as name, NULL as author_id, created_at 
    FROM blog_comments 
    ORDER BY created_at DESC 
    LIMIT 5
) ORDER BY created_at DESC LIMIT 10";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

// Recent Registered Users
$recentUsers = [];
$query = "SELECT user_id, first_name, last_name, email, created_at 
          FROM users 
          ORDER BY created_at DESC 
          LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $recentUsers[] = $row;
}

// Popular Posts
$popularPosts = [];
$query = "SELECT post_id, title, view_count 
          FROM blog_posts 
          ORDER BY view_count DESC 
          LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $popularPosts[] = $row;
}

// Set page title
$pageTitle = "Dashboard";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="bi bi-calendar"></i> This week
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Posts</h6>
                                    <h2 class="mb-0"><?= $stats['total_posts'] ?></h2>
                                </div>
                                <i class="bi bi-newspaper fs-1 opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2">
                            <a href="blog-posts.php" class="text-white small d-flex align-items-center">
                                View all <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Published Posts</h6>
                                    <h2 class="mb-0"><?= $stats['published_posts'] ?></h2>
                                </div>
                                <i class="bi bi-check-circle fs-1 opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2">
                            <a href="blog-posts.php?status=published" class="text-white small d-flex align-items-center">
                                View published <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Comments</h6>
                                    <h2 class="mb-0"><?= $stats['total_comments'] ?></h2>
                                </div>
                                <i class="bi bi-chat-left-text fs-1 opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2">
                            <a href="blog-comments.php" class="text-white small d-flex align-items-center">
                                Manage comments <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Users</h6>
                                    <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                                </div>
                                <i class="bi bi-people fs-1 opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 py-2">
                            <a href="users.php" class="text-white small d-flex align-items-center">
                                View users <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Activities -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activities</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Item</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activities)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No recent activities</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($activities as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $badgeClass = [
                                                            'post' => 'bg-primary',
                                                            'program' => 'bg-success',
                                                            'comment' => 'bg-warning'
                                                        ][$activity['type']] ?? 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= ucfirst($activity['type']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($activity['type'] === 'post'): ?>
                                                            <a href="blog-post-edit.php?id=<?= $activity['id'] ?>">
                                                                <?= htmlspecialchars($activity['name']) ?>
                                                            </a>
                                                        <?php elseif ($activity['type'] === 'program'): ?>
                                                            <a href="program-edit.php?id=<?= $activity['id'] ?>">
                                                                <?= htmlspecialchars($activity['name']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($activity['name']) ?>...
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('M j, Y H:i', strtotime($activity['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Recent Users -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Users</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentUsers)): ?>
                                <div class="text-center py-3 text-muted">
                                    No recent users
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($recentUsers as $user): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . htmlspecialchars($user['last_name'])) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                            </div>
                                            <small><?= date('M j', strtotime($user['created_at'])) ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="text-center mt-2">
                                    <a href="users.php" class="btn btn-sm btn-outline-primary">
                                        View All Users
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Popular Posts -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Popular Posts</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($popularPosts)): ?>
                                <div class="text-center py-3 text-muted">
                                    No popular posts
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($popularPosts as $post): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold">
                                                    <a href="blog-post-edit.php?id=<?= $post['post_id'] ?>">
                                                        <?= htmlspecialchars($post['title']) ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $post['view_count'] ?> views
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="text-center mt-2">
                                    <a href="blog-posts.php" class="btn btn-sm btn-outline-primary">
                                        View All Posts
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Stats -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Program Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <h3><?= $stats['total_programs'] ?></h3>
                                    <p class="text-muted mb-0">Total Programs</p>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <h3>₦<?= $stats['total_raised'] ?></h3>
                                    <p class="text-muted mb-0">Total Raised</p>
                                </div>
                                <div class="col-md-4">
                                    <h3><?= $stats['new_users'] ?></h3>
                                    <p class="text-muted mb-0">New Users (7 days)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<!-- Chart.js for future use -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Example chart - you can expand this with real data
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Posts', 'Programs', 'Users', 'Comments'],
                    datasets: [{
                        label: 'Site Statistics',
                        data: [
                            <?= $stats['total_posts'] ?>,
                            <?= $stats['total_programs'] ?>,
                            <?= $stats['total_users'] ?>,
                            <?= $stats['total_comments'] ?>
                        ],
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(13, 202, 240, 0.7)',
                            'rgba(255, 193, 7, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>