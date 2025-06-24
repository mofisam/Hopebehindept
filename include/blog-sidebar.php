<div class="ps-lg-4">
  <!-- About Widget -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <h4 class="card-title mb-3 text-success">About Our Blog</h4>
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
          <a href="blog-category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none">
            <?= htmlspecialchars($category['name']) ?>
          </a>
          <span class="badge rounded-pill bg-primary"><?= $category['post_count'] ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Recent Posts Widget -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <h4 class="card-title mb-3" style="color: #ffb420;">Recent Posts</h4>
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
      <h4 class="card-title mb-3" style="color: #ffb420;">Popular Tags</h4>
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