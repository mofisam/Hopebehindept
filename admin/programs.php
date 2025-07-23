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

// Pagination setup
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Search and filter handling
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Base query
$query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM program_category_map WHERE program_id = p.program_id) as category_count,
                 (SELECT COUNT(*) FROM success_stories WHERE program_id = p.program_id) as success_stories_count
          FROM programs p";

// Conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
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
    $query .= " JOIN program_category_map pcm ON p.program_id = pcm.program_id";
    $conditions[] = "pcm.category_id = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

// Build final query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.created_at DESC
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
$programs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM programs p";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", array_slice($conditions, 0, count($conditions)));
}

$countStmt = $conn->prepare($countQuery);
if ($types && count($params) > 2) { // More than just limit params
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, count($params) - 2));
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalPrograms = $countResult->fetch_assoc()['total'];
$countStmt->close();

// Get categories for filter dropdown
$categories = [];
$catQuery = "SELECT category_id, name FROM program_categories ORDER BY name";
$catResult = $conn->query($catQuery);
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

// Set page title
$pageTitle = "Manage Programs";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Programs</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="program-add.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add Program
                    </a>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search programs..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="upcoming" <?= $statusFilter === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
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

            <!-- Programs Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Funding</th>
                                    <th>Categories</th>
                                    <th>Stories</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($programs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No programs found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($programs as $program): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($program['title']) ?></strong>
                                                <?php if ($program['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark ms-2">Featured</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                if ($program['status'] === 'active') $statusClass = 'text-success';
                                                if ($program['status'] === 'upcoming') $statusClass = 'text-primary';
                                                if ($program['status'] === 'completed') $statusClass = 'text-secondary';
                                                ?>
                                                <span class="<?= $statusClass ?>">
                                                    <?= ucfirst($program['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?= $program['progress'] ?>%" 
                                                         aria-valuenow="<?= $program['progress'] ?>" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        <?= $program['progress'] ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                ₦<?= number_format($program['amount_raised']) ?> of ₦<?= number_format($program['funding_goal']) ?>
                                                </small>
                                            </td>
                                            <td><?= $program['category_count'] ?></td>
                                            <td><?= $program['success_stories_count'] ?></td>
                                            <td><?= date('M j, Y', strtotime($program['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../program.php?slug=<?= $program['slug'] ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="View" 
                                                       target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="program-edit.php?id=<?= $program['program_id'] ?>" 
                                                       class="btn btn-outline-secondary" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger delete-program" 
                                                            data-id="<?= $program['program_id'] ?>" 
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
                    <?php if ($totalPrograms > $perPage): ?>
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

                                <?php for ($i = 1; $i <= ceil($totalPrograms / $perPage); $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $statusFilter ?>&category=<?= $categoryFilter ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < ceil($totalPrograms / $perPage)): ?>
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

<?php include 'include/admin-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Delete program confirmation
    document.querySelectorAll('.delete-program').forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the program and all its associated data!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`program-delete.php?id=${programId}`, {
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
                                'The program has been deleted.',
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
                            'An error occurred while deleting the program.',
                            'error'
                        );
                    });
                }
            });
        });
    });
</script>