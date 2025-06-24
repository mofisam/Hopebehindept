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

// Use in your query
$query = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $perPage);

// Search and filter handling
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$programFilter = isset($_GET['program']) ? (int)$_GET['program'] : 0;
$featuredFilter = isset($_GET['featured']) ? $_GET['featured'] : '';

// Base query
$query = "SELECT s.story_id, s.title, s.content, s.author_name, s.author_image, 
                 s.rating, s.created_at, s.is_featured, p.title as program_title
          FROM success_stories s
          LEFT JOIN programs p ON s.program_id = p.program_id";

// Conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(s.title LIKE ? OR s.content LIKE ? OR s.author_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

if ($programFilter > 0) {
    $conditions[] = "s.program_id = ?";
    $params[] = $programFilter;
    $types .= 'i';
}

if ($featuredFilter !== '') {
    $conditions[] = "s.is_featured = ?";
    $params[] = $featuredFilter === 'yes' ? 1 : 0;
    $types .= 'i';
}

// Build final query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY s.created_at DESC
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
$stories = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM success_stories s";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", array_slice($conditions, 0, count($conditions)));
}

$countStmt = $conn->prepare($countQuery);
if ($types && count($params) > 2) { // More than just limit params
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, count($params) - 2));
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalStories = $countResult->fetch_assoc()['total'];
$countStmt->close();

// Get programs for filter dropdown
$programs = [];
$programQuery = "SELECT program_id, title FROM programs ORDER BY title";
$programResult = $conn->query($programQuery);
while ($row = $programResult->fetch_assoc()) {
    $programs[] = $row;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_story'])) {
        $story_id = (int)$_POST['story_id'];
        
        // Get image filename to delete later
        $imageQuery = "SELECT author_image FROM success_stories WHERE story_id = ?";
        $stmt = $conn->prepare($imageQuery);
        $stmt->bind_param("i", $story_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $story = $result->fetch_assoc();
        $stmt->close();
        
        // Delete the story
        $deleteQuery = "DELETE FROM success_stories WHERE story_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $story_id);
        
        if ($stmt->execute()) {
            // Delete the image file if exists
            if (!empty($story['author_image'])) {
                $imagePath = "../uploads/success-stories/" . $story['author_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $_SESSION['success_message'] = "Success story deleted successfully!";
            header("Location: success-stories.php");
            exit();
        } else {
            $errors[] = "Error deleting story: " . $conn->error;
        }
    } elseif (isset($_POST['toggle_featured'])) {
        $story_id = (int)$_POST['story_id'];
        $is_featured = (int)$_POST['is_featured'];
        
        $updateQuery = "UPDATE success_stories SET is_featured = ? WHERE story_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $is_featured, $story_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Story featured status updated!";
            header("Location: success-stories.php");
            exit();
        } else {
            $errors[] = "Error updating story: " . $conn->error;
        }
    }
}

// Set page title
$pageTitle = "Manage Success Stories";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Success Stories</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="success-story-add.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add New Story
                    </a>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search stories..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="program">
                                    <option value="0">All Programs</option>
                                    <?php foreach ($programs as $program): ?>
                                        <option value="<?= $program['program_id'] ?>" <?= $programFilter == $program['program_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($program['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="featured">
                                    <option value="">All Stories</option>
                                    <option value="yes" <?= $featuredFilter === 'yes' ? 'selected' : '' ?>>Featured Only</option>
                                    <option value="no" <?= $featuredFilter === 'no' ? 'selected' : '' ?>>Not Featured</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Success Stories Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Program</th>
                                    <th>Rating</th>
                                    <th>Featured</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stories)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No success stories found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stories as $story): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($story['title']) ?></strong>
                                                <div class="text-muted small mt-1">
                                                    <?= substr(strip_tags($story['content']), 0, 100) ?><?= strlen($story['content']) > 100 ? '...' : '' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($story['author_image'])): ?>
                                                        <img src="../uploads/success-stories/<?= htmlspecialchars($story['author_image']) ?>" 
                                                             class="rounded-circle me-2" width="40" height="40" alt="<?= htmlspecialchars($story['author_name']) ?>">
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($story['author_name']) ?>
                                                </div>
                                            </td>
                                            <td><?= $story['program_title'] ? htmlspecialchars($story['program_title']) : '—' ?></td>
                                            <td>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?= $i <= $story['rating'] ? '-fill text-warning' : '' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="story_id" value="<?= $story['story_id'] ?>">
                                                    <input type="hidden" name="is_featured" value="<?= $story['is_featured'] ? 0 : 1 ?>">
                                                    <button type="submit" name="toggle_featured" class="btn btn-sm <?= $story['is_featured'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                                                        <i class="bi <?= $story['is_featured'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                                        <?= $story['is_featured'] ? 'Featured' : 'Feature' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($story['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="success-story-edit.php?id=<?= $story['story_id'] ?>" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this success story?');">
                                                        <input type="hidden" name="story_id" value="<?= $story['story_id'] ?>">
                                                        <button type="submit" name="delete_story" class="btn btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalStories > $perPage): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&program=<?= $programFilter ?>&featured=<?= $featuredFilter ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= ceil($totalStories / $perPage); $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&program=<?= $programFilter ?>&featured=<?= $featuredFilter ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < ceil($totalStories / $perPage)): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&program=<?= $programFilter ?>&featured=<?= $featuredFilter ?>">
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

<?php include 'include/admin-footer.php'; ?>

<style>
    .rating {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>