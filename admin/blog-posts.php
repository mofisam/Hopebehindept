<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../include/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Pagination setup
$perPage = (int)get_setting('posts_per_page', 10);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $perPage;
$query = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $perPage);

// Search and filter handling
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Base query
$query = "SELECT p.post_id, p.title, p.slug, p.status, p.created_at, p.published_at, 
                 u.first_name, u.last_name, COUNT(c.comment_id) as comment_count
          FROM blog_posts p
          JOIN users u ON p.author_id = u.user_id
          LEFT JOIN blog_comments c ON p.post_id = c.post_id";

// Conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($statusFilter)) {
    $conditions[] = "p.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($categoryFilter > 0) {
    $query .= " JOIN blog_post_categories pc ON p.post_id = pc.post_id";
    $conditions[] = "pc.category_id = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

// Build final query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY p.post_id
            ORDER BY p.created_at DESC
            LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= 'ii';

// Prepare and execute
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM blog_posts p";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", array_slice($conditions, 0, count($conditions)));
}

$countStmt = $conn->prepare($countQuery);
if ($types && count($params) > 2) { // More than just limit params
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, count($params) - 2));
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalPosts = $countResult->fetch_assoc()['total'];
$countStmt->close();

// Get categories for filter dropdown
$categories = [];
$catQuery = "SELECT category_id, name FROM blog_categories ORDER BY name";
$catResult = $conn->query($catQuery);
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'include/admin-header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'include/admin-sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Blog Posts</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="blog-post-add.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add New Post
                        </a>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                                        <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="category">
                                        <option value="0">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= $categoryFilter == $category['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Blog Posts Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Comments</th>
                                        <th>Created</th>
                                        <th>Published</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No blog posts found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($post['title']) ?></strong>
                                                    <div class="text-muted small">/<?= htmlspecialchars($post['slug']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></td>
                                                <td>
                                                    <?php 
                                                    $statusClass = '';
                                                    if ($post['status'] === 'published') $statusClass = 'text-success';
                                                    if ($post['status'] === 'draft') $statusClass = 'text-warning';
                                                    if ($post['status'] === 'archived') $statusClass = 'text-danger';
                                                    ?>
                                                    <span class="<?= $statusClass ?>">
                                                        <?= ucfirst($post['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $post['comment_count'] ?></td>
                                                <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                                                <td>
                                                    <?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '—' ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../blog-single.php?slug=<?= $post['slug'] ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View" 
                                                           target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="blog-post-edit.php?id=<?= $post['post_id'] ?>" 
                                                           class="btn btn-outline-secondary" 
                                                           title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button class="btn btn-outline-danger delete-post" 
                                                                data-id="<?= $post['post_id'] ?>" 
                                                                title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPosts > $perPage): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>&category=<?= $categoryFilter ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= ceil($totalPosts / $perPage); $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" 
                                               href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>&category=<?= $categoryFilter ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < ceil($totalPosts / $perPage)): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>&category=<?= $categoryFilter ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Delete post confirmation
        document.querySelectorAll('.delete-post').forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`blog-post-delete.php?id=${postId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'The blog post has been deleted.',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message || 'Something went wrong.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the post.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>