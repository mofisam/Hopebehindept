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

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Set page title
$pageTitle = "Search Results";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
    <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Search</h1>
            </div>

            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="search.php" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="q" 
                                   value="<?= htmlspecialchars($search_query) ?>" 
                                   placeholder="Search everything..." required>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="type">
                                <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>All Content</option>
                                <option value="posts" <?= $search_type === 'posts' ? 'selected' : '' ?>>Blog Posts</option>
                                <option value="pages" <?= $search_type === 'pages' ? 'selected' : '' ?>>Pages</option>
                                <option value="users" <?= $search_type === 'users' ? 'selected' : '' ?>>Users</option>
                                <option value="comments" <?= $search_type === 'comments' ? 'selected' : '' ?>>Comments</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($search_query)): ?>
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Search blog posts
                        if ($search_type === 'all' || $search_type === 'posts') {
                            $posts = [];
                            $query = "SELECT p.*, u.first_name, u.last_name 
                                      FROM blog_posts p
                                      JOIN users u ON p.author_id = u.user_id
                                      WHERE (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
                                      ORDER BY p.created_at DESC";
                            
                            $stmt = $conn->prepare($query);
                            $search_param = "%$search_query%";
                            $stmt->bind_param("sss", $search_param, $search_param, $search_param);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0): ?>
                                <h3 class="h5 mb-3">Blog Posts (<?= $result->num_rows ?>)</h3>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($post = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <a href="blog-post-edit.php?id=<?= $post['post_id'] ?>">
                                                            <?= htmlspecialchars($post['title']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?= $post['status'] === 'published' ? 'bg-success' : '' ?>
                                                            <?= $post['status'] === 'draft' ? 'bg-warning text-dark' : '' ?>
                                                            <?= $post['status'] === 'archived' ? 'bg-secondary' : '' ?>">
                                                            <?= ucfirst($post['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                                                    <td>
                                                        <a href="blog-post-edit.php?id=<?= $post['post_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif;
                            $stmt->close();
                        }

                        // Search users
                        if ($search_type === 'all' || $search_type === 'users') {
                            $users = [];
                            $query = "SELECT u.*, r.role_name 
                                      FROM users u
                                      JOIN user_roles r ON u.role_id = r.role_id
                                      WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)
                                      ORDER BY u.created_at DESC";
                            
                            $stmt = $conn->prepare($query);
                            $search_param = "%$search_query%";
                            $stmt->bind_param("sss", $search_param, $search_param, $search_param);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0): ?>
                                <h3 class="h5 mb-3">Users (<?= $result->num_rows ?>)</h3>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <a href="user-edit.php?id=<?= $user['user_id'] ?>">
                                                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                                    <td>
                                                        <a href="user-edit.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif;
                            $stmt->close();
                        }

                        // Search comments
                        if ($search_type === 'all' || $search_type === 'comments') {
                            $comments = [];
                            $query = "SELECT c.*, p.title as post_title, p.slug as post_slug 
                                      FROM blog_comments c
                                      JOIN blog_posts p ON c.post_id = p.post_id
                                      WHERE (c.content LIKE ? OR c.author_name LIKE ? OR c.author_email LIKE ?)
                                      ORDER BY c.created_at DESC";
                            
                            $stmt = $conn->prepare($query);
                            $search_param = "%$search_query%";
                            $stmt->bind_param("sss", $search_param, $search_param, $search_param);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0): ?>
                                <h3 class="h5 mb-3">Comments (<?= $result->num_rows ?>)</h3>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Comment</th>
                                                <th>Author</th>
                                                <th>Post</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($comment = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;">
                                                            <?= htmlspecialchars($comment['content']) ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($comment['author_name']) ?>
                                                        <small class="text-muted d-block"><?= htmlspecialchars($comment['author_email']) ?></small>
                                                    </td>
                                                    <td>
                                                        <a href="../blog-single.php?slug=<?= $comment['post_slug'] ?>" target="_blank">
                                                            <?= htmlspecialchars($comment['post_title']) ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                            <?= $comment['status'] === 'approved' ? 'bg-success' : '' ?>
                                                            <?= $comment['status'] === 'pending' ? 'bg-warning text-dark' : '' ?>
                                                            <?= $comment['status'] === 'spam' ? 'bg-danger' : '' ?>">
                                                            <?= ucfirst($comment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($comment['created_at'])) ?></td>
                                                    <td>
                                                        <a href="blog-comments.php?status=all#comment-<?= $comment['comment_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif;
                            $stmt->close();
                        }

                        // Show message if no results found
                        if (($search_type === 'all' || $search_type === 'posts') && 
                            ($search_type === 'all' || $search_type === 'users') && 
                            ($search_type === 'all' || $search_type === 'comments') && 
                            empty($posts) && empty($users) && empty($comments)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-search fs-1 text-muted"></i>
                                <h4 class="mt-3">No results found</h4>
                                <p class="text-muted">Try different search terms or search a specific content type.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <h4 class="mt-3">Search the Admin Panel</h4>
                        <p class="text-muted">Enter your search terms above to find content.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<style>
    .search-highlight {
        background-color: #fff3cd;
        font-weight: 500;
    }
    .table td {
        vertical-align: middle;
    }
</style>

<script>
    // Highlight search terms in results
    document.addEventListener('DOMContentLoaded', function() {
        const searchQuery = "<?= addslashes($search_query) ?>";
        if (searchQuery) {
            const regex = new RegExp(searchQuery, 'gi');
            const elements = document.querySelectorAll('.table td');
            
            elements.forEach(el => {
                const html = el.innerHTML;
                const highlighted = html.replace(regex, match => 
                    `<span class="search-highlight">${match}</span>`);
                el.innerHTML = highlighted;
            });
        }
    });
</script>