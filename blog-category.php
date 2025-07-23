<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/include/blog_functions.php';

// Get category slug from URL
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($category_slug)) {
    header("Location: blog.php");
    exit();
}

// Get category information
$category = [];
$stmt = $conn->prepare("SELECT * FROM blog_categories WHERE slug = ?");
$stmt->bind_param("s", $category_slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: blog.php");
    exit();
}

$category = $result->fetch_assoc();
$stmt->close();

// Get posts in this category
$posts = [];
$query = "SELECT p.*, u.first_name, u.last_name 
          FROM blog_posts p
          JOIN users u ON p.author_id = u.user_id
          JOIN blog_post_categories pc ON p.post_id = pc.post_id
          JOIN blog_categories c ON pc.category_id = c.category_id
          WHERE c.slug = ? AND p.status = 'published'
          ORDER BY p.published_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $category_slug);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();

// Get recent posts for sidebar
$recent_posts = getRecentPosts($conn, 5);
$categories = getBlogCategories($conn);
$popular_tags = getPopularTags($conn, 10);

// Set page title
$page_title = $category['name'] . ' - Blog Category';

// Include header
include __DIR__ . '/include/header.php';
?>

<!-- Category Header -->
<section class="category-header py-5 bg-light" style="background: linear-gradient(90deg, #feb21e 0%, #27a263 70%, #27a263 100%);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 text-white"><?= htmlspecialchars($category['name']) ?></h1>
                <?php if (!empty($category['description'])): ?>
                    <p class="lead"><?= htmlspecialchars($category['description']) ?></p>
                <?php endif; ?>
                <p class="text-muted"><?= count($posts) ?> article<?= count($posts) !== 1 ? 's' : '' ?> in this category</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Posts Column -->
        <div class="col-lg-8">
            <?php if (empty($posts)): ?>
                <div class="card mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-newspaper fs-1 text-muted"></i>
                        <h3 class="mt-3">No posts found</h3>
                        <p>There are no published posts in this category yet.</p>
                        <a href="/blog.php" class="btn btn-primary">Browse All Posts</a>
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
                                <img src="/uploads/avatars/<?= htmlspecialchars($post['avatar'] ?? 'default-avatar.jpg') ?>" 
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
                                <a href="/blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            <p class="card-text"><?= htmlspecialchars($post['excerpt']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="/blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-sm btn-outline-primary">
                                    Read More
                                </a>
                                <small class="text-muted">
                                    <i class="bi bi-chat-left-text"></i> <?= $comment_count ?> comment<?= $comment_count !== 1 ? 's' : '' ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <?php 
                            $post_tags = getPostTags($conn, $post['post_id']);
                            foreach ($post_tags as $tag): 
                            ?>
                                <a href="blog-tag.php?slug=<?= htmlspecialchars($tag['slug']) ?>" class="badge bg-light text-dark me-1">
                                    #<?= htmlspecialchars($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination would go here if implemented -->
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