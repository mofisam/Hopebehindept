<?php
session_start();
require_once 'config/db.php'; 
include 'include/header.php';
include 'include/blog_functions.php';

// Get blog data
$featuredPost = getFeaturedPost($conn);
$recentPosts = getRecentPosts($conn);
$categories = getBlogCategories($conn);
$sidebarPosts = getRecentPostsForSidebar($conn);
$popularTags = getPopularTags($conn);
?>

<!-- Blog Hero Section -->
<section class="blog-hero py-5" style="background: linear-gradient(135deg, #ffb420 0%, #ff8c00 50%, #ff6b00 100%);">
  <div class="container py-5">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold text-white mb-4">Our Blog</h1>
        <p class="lead text-white mb-4">Insights, stories and updates on our mission to improve financial freedom globally</p>
        <div class="search-bar mx-auto" style="max-width: 500px;">
          <form class="d-flex" method="GET" action="blog-search.php">
            <input class="form-control me-2" type="search" name="q" placeholder="Search articles..." aria-label="Search">
            <button class="btn btn-light" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Main Blog Content -->
<div class="container py-5">
  <div class="row">
    <!-- Main Content Area -->
    <div class="col-lg-8">
      <!-- Featured Post -->
      <?php if ($featuredPost): ?>
      <div class="card mb-5 border-0 shadow-lg">
        <div class="position-relative">
          <img src="/uploads/blog/<?= htmlspecialchars($featuredPost['featured_image'] ?? 'default-featured.jpg') ?>" 
               class="card-img-top" alt="<?= htmlspecialchars($featuredPost['title']) ?>" style="height: 400px; object-fit: cover;">
          <div class="position-absolute top-0 start-0 bg-success text-white px-3 py-2">
            Featured
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex mb-3">
            <span class="text-muted me-3">
              <i class="far fa-calendar me-2"></i>
              <?= date('F j, Y', strtotime($featuredPost['published_at'])) ?>
            </span>
            <span class="text-muted">
              <i class="far fa-user me-2"></i>
              By <?= htmlspecialchars($featuredPost['first_name'] . ' ' . $featuredPost['last_name']) ?>
            </span>
          </div>
          <h2 class="card-title mb-3"><?= htmlspecialchars($featuredPost['title']) ?></h2>
          <p class="card-text"><?= htmlspecialchars($featuredPost['excerpt']) ?></p>
          <a href="blog-single.php?slug=<?= htmlspecialchars($featuredPost['slug']) ?>" class="btn btn-success px-4">Read More</a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Blog Posts Grid -->
      <div class="row g-4">
        <?php foreach ($recentPosts as $post): 
          $commentCount = getPostCommentsCount($conn, $post['post_id']);
        ?>
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm">
            <img src="/uploads/blog/<?= htmlspecialchars($post['featured_image'] ?? 'default-post.jpg') ?>" 
                 class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <div class="d-flex mb-2">
                <span class="text-muted small me-3">
                  <i class="far fa-calendar me-1"></i>
                  <?= date('M j, Y', strtotime($post['published_at'])) ?>
                </span>
                <span class="text-muted small">
                  <i class="far fa-comment me-1"></i>
                  <?= $commentCount ?> Comment<?= $commentCount != 1 ? 's' : '' ?>
                </span>
              </div>
              <h3 class="h5 card-title"><?= htmlspecialchars($post['title']) ?></h3>
              <p class="card-text"><?= htmlspecialchars($post['excerpt']) ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-sm btn-outline-success">Read More</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <nav aria-label="Blog pagination" class="mt-5">
        <ul class="pagination justify-content-center">
          <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
          </li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item">
            <a class="page-link" href="#">Next</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="ps-lg-4">
        <!-- About Widget -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3 text-success" >About Our Blog</h4>
            <p class="card-text">Stay updated with our latest initiatives, success stories, and insights into financial freedom challenges and solutions.</p>
            <div class="d-flex">
              <a href="about.php" class="btn btn-sm btn-outline-success me-2">Learn More</a>
              <a href="subscribe.php" class="btn btn-sm btn-success" style="background-color: #ffb420; border-color: #ffb420;">Subscribe</a>
            </div>
          </div>
        </div>

        <!-- Categories Widget -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3 text-success" >Categories</h4>
            <ul class="list-group list-group-flush">
              <?php foreach ($categories as $category): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="blog-category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none text-success">
                  <?= htmlspecialchars($category['name']) ?>
                </a>
                <span class="badge rounded-pill bg-warning"><?= $category['post_count'] ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- Recent Posts Widget -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3 text-success" >Recent Posts</h4>
            <?php foreach ($sidebarPosts as $post): ?>
            <div class="mb-3 d-flex">
              <img src="/uploads/blog/<?= htmlspecialchars($post['featured_image'] ?? 'default-thumbnail.jpg') ?>" 
                   alt="<?= htmlspecialchars($post['title']) ?>" class="rounded me-3" width="80" height="60" style="object-fit: cover;">
              <div>
                <h6 class="mb-1">
                  <a href="blog-single.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none">
                    <?= htmlspecialchars($post['title']) ?>
                  </a>
                </h6>
                <small class="text-muted"><?= date('M j, Y', strtotime($post['created_at'])) ?></small>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tags Widget -->
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3 text-success">Popular Tags</h4>
            <div class="tags">
              <?php foreach ($popularTags as $tag): ?>
              <a href="blog-tag.php?slug=<?= htmlspecialchars($tag['slug']) ?>" class="btn btn-sm btn-outline-secondary mb-2 me-1">
                #<?= htmlspecialchars($tag['name']) ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php 
include 'include/footer.php';
?>