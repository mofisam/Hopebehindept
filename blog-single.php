<?php
session_start();
require_once 'config/db.php'; 
include 'include/blog_functions.php';

if (!isset($_GET['slug'])) {
    header("Location: blog.php");
    exit();
}

$postSlug = $_GET['slug'];

// Get post data
$query = "SELECT p.*, u.first_name, u.last_name, u.avatar 
          FROM blog_posts p
          JOIN users u ON p.author_id = u.user_id
          WHERE p.slug = ? AND p.status = 'published'";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $postSlug);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: blog.php");
    exit();
}

// Increment view count
$conn->query("UPDATE blog_posts SET view_count = view_count + 1 WHERE post_id = {$post['post_id']}");

// Get post categories
$categories = [];
$query = "SELECT c.name, c.slug 
          FROM blog_categories c
          JOIN blog_post_categories pc ON c.category_id = pc.category_id
          WHERE pc.post_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post['post_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();

// Get post tags
$tags = [];
$query = "SELECT t.name, t.slug 
          FROM blog_tags t
          JOIN blog_post_tags pt ON t.tag_id = pt.tag_id
          WHERE pt.post_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post['post_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}
$stmt->close();

// Get post comments
$comments = [];
$query = "SELECT * FROM blog_comments 
          WHERE post_id = ? AND status = 'approved' AND parent_id IS NULL
          ORDER BY created_at DESC";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post['post_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

// Get related posts
$relatedPosts = [];
$query = "SELECT p.post_id, p.title, p.slug, p.featured_image, p.excerpt, p.published_at
          FROM blog_posts p
          JOIN blog_post_categories pc ON p.post_id = pc.post_id
          WHERE pc.category_id IN (
              SELECT category_id FROM blog_post_categories WHERE post_id = ?
          ) AND p.post_id != ? AND p.status = 'published'
          GROUP BY p.post_id
          ORDER BY p.published_at DESC
          LIMIT 3";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $post['post_id'], $post['post_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $relatedPosts[] = $row;
}
$stmt->close();
include 'include/header.php';
?>

<section class="py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-8">
        <!-- Post Content -->
        <article>
          <h1 class="display-5 fw-bold mb-4"><?= htmlspecialchars($post['title']) ?></h1>
          
          <!-- Post Meta -->
          <div class="d-flex align-items-center mb-4">
            <img src="/uploads/avatars/<?= htmlspecialchars($post['avatar'] ?? 'default-avatar.jpg') ?>" 
                 class="rounded-circle me-3" width="50" height="50" alt="<?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>">
            <div>
              <h6 class="mb-0"><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></h6>
              <small class="text-muted">
                <i class="far fa-calendar me-1"></i>
                <?= date('F j, Y', strtotime($post['published_at'])) ?>
                <span class="mx-2">•</span>
                <i class="far fa-clock me-1"></i>
                <?= ceil(str_word_count($post['content']) / 200) ?> min read
              </small>
            </div>
          </div>
          
          <!-- Featured Image -->
          <img src="uploads/blog/<?= htmlspecialchars($post['featured_image'] ?? 'default-featured.jpg') ?>" 
               class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($post['title']) ?>">
          
          <!-- Post Content -->
          <div class="post-content mb-5">
            <?= $post['content'] ?>
          </div>
          
          <!-- Categories and Tags -->
          <div class="d-flex flex-wrap gap-3 mb-5">
            <?php if (!empty($categories)): ?>
            <div>
              <span class="fw-bold me-2">Categories:</span>
              <?php foreach ($categories as $index => $category): ?>
                <a href="blog-category.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none">
                  <?= htmlspecialchars($category['name']) ?>
                </a>
                <?= $index < count($categories) - 1 ? ',' : '' ?>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($tags)): ?>
            <div>
              <span class="fw-bold me-2">Tags:</span>
              <?php foreach ($tags as $index => $tag): ?>
                <a href="blog-tag.php?slug=<?= htmlspecialchars($tag['slug']) ?>" class="text-decoration-none">
                  #<?= htmlspecialchars($tag['name']) ?>
                </a>
                <?= $index < count($tags) - 1 ? ',' : '' ?>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Author Bio -->
          <div class="card mb-5 border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <img src="/uploads/avatars/<?= htmlspecialchars($post['avatar'] ?? 'default-avatar.jpg') ?>" 
                     class="rounded-circle me-4" width="100" height="100" alt="<?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>">
                <div>
                  <h5 class="card-title">About <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></h5>
                  <p class="card-text">Financial expert with over 10 years of experience in debt relief and financial education programs.</p>
                </div>
              </div>
            </div>
          </div>
        </article>
        
        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
        <section class="mb-5">
          <h3 class="h4 mb-4">Related Articles</h3>
          <div class="row g-4">
            <?php foreach ($relatedPosts as $relatedPost): ?>
            <div class="col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <img src="uploads/blog/<?= htmlspecialchars($relatedPost['featured_image'] ?? 'default-post.jpg') ?>" 
                     class="card-img-top" alt="<?= htmlspecialchars($relatedPost['title']) ?>" style="height: 180px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($relatedPost['title']) ?></h5>
                  <p class="card-text"><?= htmlspecialchars($relatedPost['excerpt']) ?></p>
                </div>
                <div class="card-footer bg-transparent border-0">
                  <a href="blog-single.php?slug=<?= htmlspecialchars($relatedPost['slug']) ?>" class="btn btn-sm btn-outline-success">Read More</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>
        
        <!-- Comments Section -->
        <section class="mb-5">
          <h3 class="h4 mb-4">Comments (<?= count($comments) ?>)</h3>
          
          <?php if (empty($comments)): ?>
            <div class="alert alert-info">No comments yet. Be the first to comment!</div>
          <?php else: ?>
            <div class="comments-list">
              <?php foreach ($comments as $comment): ?>
              <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img src="https://ui-avatars.com/api/?name=<?= urlencode($comment['author_name']) ?>&background=random" 
                           class="rounded-circle me-3" width="50" height="50" alt="<?= htmlspecialchars($comment['author_name']) ?>">
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1"><?= htmlspecialchars($comment['author_name']) ?></h6>
                      <small class="text-muted">
                        <i class="far fa-calendar me-1"></i>
                        <?= date('M j, Y', strtotime($comment['created_at'])) ?>
                      </small>
                      <p class="mt-2 mb-0"><?= htmlspecialchars($comment['content']) ?></p>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Comment Form -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h4 class="h5 mb-4">Leave a Comment</h4>
              <form method="POST" action="process-comment.php">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="author_name" name="author_name" placeholder="Your Name" required>
                      <label for="author_name">Your Name</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="email" class="form-control" id="author_email" name="author_email" placeholder="Your Email" required>
                      <label for="author_email">Your Email</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating">
                      <textarea class="form-control" id="comment_content" name="comment_content" 
                                placeholder="Your Comment" style="height: 120px" required></textarea>
                      <label for="comment_content">Your Comment</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-success">Post Comment</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </section>
      </div>
      
      <!-- Sidebar -->
      <div class="col-lg-4">
        <?php include 'include/blog-sidebar.php'; ?>
      </div>
    </div>
  </div>
</section>

<?php include 'include/footer.php'; ?>
<script></scr