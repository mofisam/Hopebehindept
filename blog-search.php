<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/include/blog_functions.php';

// Get search query from URL
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Set page title
$page_title = $search_query ? "Search Results for \"$search_query\"" : "Blog Search";

// Include header
include __DIR__ . '/include/header.php';

// Get search results if query exists
$posts = [];
if (!empty($search_query)) {
    $query = "SELECT p.*, u.first_name, u.last_name 
              FROM blog_posts p
              JOIN users u ON p.author_id = u.user_id
              WHERE (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
              AND p.status = 'published'
              ORDER BY p.published_at DESC";
    
    $search_param = "%$search_query%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}

// Get data for sidebar
$recent_posts = getRecentPosts($conn, 5);
$categories = getBlogCategories($conn);
$popular_tags = getPopularTags($conn, 10);
?>

<!-- Search Hero Section -->
<section class="search-hero py-5 bg-light" style="background: linear-gradient(90deg, #feb21e 0%, #27a263 70%, #27a263 100%);">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 mb-4 text-white"><?= htmlspecialchars($page_title) ?></h1>
                <form action="blog-search.php" method="get" class="search-form">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg " 
                               name="q" value="<?= htmlspecialchars($search_query) ?>" 
                               placeholder="Search articles..." required>
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
                <?php if (!empty($search_query)): ?>
                    <p class="text-muted">Found <?= count($posts) ?> result<?= count($posts) !== 1 ? 's' : '' ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Search Results Column -->
        <div class="col-lg-8">
            <?php if (empty($search_query)): ?>
                <div class="card mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <h3 class="mt-3">What are you looking for?</h3>
                        <p>Enter your search terms above to find blog posts.</p>
                    </div>
                </div>
            <?php elseif (empty($posts)): ?>
                <div class="card mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                        <h3 class="mt-3">No results found</h3>
                        <p>Try different or more general keywords.</p>
                        <a href="blog.php" class="btn btn-success">Browse All Posts</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): 
                    $comment_count = getPostCommentsCount($conn, $post['post_id']);
                ?>
                    <article class="card mb-4">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?= BASE_URL ?>uploads/blog/<?= htmlspecialchars($post['featured_image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 style="height: 300px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= BASE_URL ?>uploads/avatars/<?= htmlspecialchars($post['avatar'] ?? 'default-avatar.jpg') ?>" 
                                     class="rounded-circle me-3" width="40" height="40" 
                                     alt="<?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></h6>
                                    <small class="text-muted">
                                        <?= date('F j, Y', strtotime($post['published_at'])) ?>
                                        <span class="mx-1">•</span>
                                        <?= ceil(str_word_count($post['content']) / 200) ?> min read
                                    </small>
                                </div>
                            </div>
                            <h2 class="card-title h4">
                                <a href="blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none text-success">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            <p class="card-text"><?= htmlspecialchars($post['excerpt']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?= BASE_URL ?>blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-sm btn-outline-warning">
                                    Read More
                                </a>
                                <small class="text-muted">
                                    <i class="bi bi-chat-left-text"></i> <?= $comment_count ?> comment<?= $comment_count !== 1 ? 's' : '' ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <?php 
                            $post_categories = getPostCategories($conn, $post['post_id']);
                            foreach ($post_categories as $category): 
                            ?>
                                <a href="<?= BASE_URL ?>blog-category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="badge bg-light text-dark me-1">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <?php include __DIR__ . '/include/blog-sidebar.php'; ?>
        </div>
    </div>
</div>

<?php 
// Include footer
include __DIR__ . '/include/footer.php';
?>
<Style>
    /* Search page specific styles */
.search-hero {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 0 2px;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .search-hero {
        padding: 2rem 0;
    }
    .search-hero h1 {
        font-size: 2rem;
    }
}
</Style>