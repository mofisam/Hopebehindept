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
// Handle comment status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $comment_id = (int)$_POST['comment_id'];
        $new_status = $_POST['status'];
        
        $valid_statuses = ['pending', 'approved', 'spam'];
        if (in_array($new_status, $valid_statuses)) {
            $stmt = $conn->prepare("UPDATE blog_comments SET status = ? WHERE comment_id = ?");
            $stmt->bind_param("si", $new_status, $comment_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Comment status updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating comment status: " . $conn->error;
            }
        }
    } elseif (isset($_POST['delete_comment'])) {
        $comment_id = (int)$_POST['comment_id'];
        
        $stmt = $conn->prepare("DELETE FROM blog_comments WHERE comment_id = ?");
        $stmt->bind_param("i", $comment_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Comment deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting comment: " . $conn->error;
        }
    }
    
    header("Location: blog-comments.php");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$post_filter = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$query = "SELECT c.*, p.title as post_title, p.slug as post_slug 
          FROM blog_comments c
          JOIN blog_posts p ON c.post_id = p.post_id
          WHERE 1=1";
          
$params = [];
$types = '';

if (!empty($status_filter)) {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($post_filter > 0) {
    $query .= " AND c.post_id = ?";
    $params[] = $post_filter;
    $types .= 'i';
}

if (!empty($search_query)) {
    $query .= " AND (c.content LIKE ? OR c.author_name LIKE ? OR c.author_email LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY c.created_at DESC";

// Prepare and execute
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);

// Get all posts for filter dropdown
$posts = [];
$post_result = $conn->query("SELECT post_id, title FROM blog_posts ORDER BY title");
while ($row = $post_result->fetch_assoc()) {
    $posts[] = $row;
}

// Set page title
$pageTitle = "Manage Comments";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Blog Comments</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="blog-comments.php" class="btn btn-outline-secondary <?= empty($status_filter) ? 'active' : '' ?>">All</a>
                        <a href="blog-comments.php?status=pending" class="btn btn-outline-warning <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
                        <a href="blog-comments.php?status=approved" class="btn btn-outline-success <?= $status_filter === 'approved' ? 'active' : '' ?>">Approved</a>
                        <a href="blog-comments.php?status=spam" class="btn btn-outline-danger <?= $status_filter === 'spam' ? 'active' : '' ?>">Spam</a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <select class="form-select" name="post_id">
                                <option value="0">All Posts</option>
                                <?php foreach ($posts as $post): ?>
                                    <option value="<?= $post['post_id'] ?>" <?= $post_filter == $post['post_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($post['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="search" placeholder="Search comments..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <?php if ($status_filter || $post_filter || $search_query): ?>
                                <a href="blog-comments.php" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comments Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-square-text fs-1 text-muted"></i>
                            <h5 class="mt-3">No comments found</h5>
                            <p class="text-muted">Try adjusting your filters</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Comment</th>
                                        <th>Author</th>
                                        <th>Post</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($comment['content']) ?>">
                                                    <?= htmlspecialchars($comment['content']) ?>
                                                </div>
                                                <?php if ($comment['parent_id']): ?>
                                                    <small class="text-muted">(Reply to comment #<?= $comment['parent_id'] ?>)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($comment['author_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($comment['author_email']) ?></small>
                                            </td>
                                            <td>
                                                <a href="../blog-single.php?slug=<?= $comment['post_slug'] ?>" target="_blank">
                                                    <?= htmlspecialchars($comment['post_title']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M j, Y H:i', strtotime($comment['created_at'])) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= $comment['status'] === 'approved' ? 'bg-success' : '' ?>
                                                    <?= $comment['status'] === 'pending' ? 'bg-warning text-dark' : '' ?>
                                                    <?= $comment['status'] === 'spam' ? 'bg-danger' : '' ?>">
                                                    <?= ucfirst($comment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-gear"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="post" class="dropdown-item">
                                                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                                                <input type="hidden" name="status" value="approved">
                                                                <button type="submit" name="update_status" class="btn btn-link p-0 text-success">
                                                                    <i class="bi bi-check-circle"></i> Approve
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="post" class="dropdown-item">
                                                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                                                <input type="hidden" name="status" value="pending">
                                                                <button type="submit" name="update_status" class="btn btn-link p-0 text-warning">
                                                                    <i class="bi bi-clock"></i> Set Pending
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="post" class="dropdown-item">
                                                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                                                <input type="hidden" name="status" value="spam">
                                                                <button type="submit" name="update_status" class="btn btn-link p-0 text-danger">
                                                                    <i class="bi bi-exclamation-octagon"></i> Mark as Spam
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="post" class="dropdown-item" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                                                <button type="submit" name="delete_comment" class="btn btn-link p-0 text-danger">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<style>
    .table td {
        vertical-align: middle;
    }
    .dropdown-item form {
        display: block;
        padding: 0.25rem 1rem;
        color: #212529;
    }
    .dropdown-item form:hover {
        background-color: #f8f9fa;
        color: #16181b;
        text-decoration: none;
    }
    .dropdown-item button {
        width: 100%;
        text-align: left;
        background: none;
        border: none;
    }
</style>