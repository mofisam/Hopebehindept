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

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $postId > 0;

// Initialize post data
$post = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'author_id' => $_SESSION['user_id'],
    'status' => 'draft',
    'is_featured' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'published_at' => ''
];

$selectedCategories = [];
$selectedTags = [];

// Get all categories and tags
$categories = [];
$tags = [];
$authors = [];

// Fetch categories
$catQuery = "SELECT category_id, name FROM blog_categories ORDER BY name";
$catResult = $conn->query($catQuery);
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch tags
$tagQuery = "SELECT tag_id, name FROM blog_tags ORDER BY name";
$tagResult = $conn->query($tagQuery);
while ($row = $tagResult->fetch_assoc()) {
    $tags[] = $row;
}

// Fetch authors (users with role_id 1 or 2)
$authorQuery = "SELECT user_id, first_name, last_name FROM users WHERE role_id IN (1, 2) ORDER BY first_name, last_name";
$authorResult = $conn->query($authorQuery);
while ($row = $authorResult->fetch_assoc()) {
    $authors[] = $row;
}

// If editing, fetch the post data
if ($isEdit) {
    $postQuery = "SELECT * FROM blog_posts WHERE post_id = ?";
    $stmt = $conn->prepare($postQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: blog-posts.php");
        exit();
    }
    
    $post = $result->fetch_assoc();
    $stmt->close();
    
    // Fetch selected categories
    $catMapQuery = "SELECT category_id FROM blog_post_categories WHERE post_id = ?";
    $stmt = $conn->prepare($catMapQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $catResult = $stmt->get_result();
    while ($row = $catResult->fetch_assoc()) {
        $selectedCategories[] = $row['category_id'];
    }
    $stmt->close();
    
    // Fetch selected tags
    $tagMapQuery = "SELECT tag_id FROM blog_post_tags WHERE post_id = ?";
    $stmt = $conn->prepare($tagMapQuery);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $tagResult = $stmt->get_result();
    while ($row = $tagResult->fetch_assoc()) {
        $selectedTags[] = $row['tag_id'];
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $metaTitle = trim($_POST['meta_title']);
    $metaDescription = trim($_POST['meta_description']);
    $authorId = (int)$_POST['author_id'];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    // Validate required fields
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if (empty($excerpt)) $errors[] = "Excerpt is required";
    if (empty($content)) $errors[] = "Content is required";
    
    // Check if slug is unique (for new posts or when slug changes)
    $slugCheckQuery = "SELECT post_id FROM blog_posts WHERE slug = ?" . ($isEdit ? " AND post_id != ?" : "");
    $stmt = $conn->prepare($slugCheckQuery);
    if ($isEdit) {
        $stmt->bind_param("si", $slug, $postId);
    } else {
        $stmt->bind_param("s", $slug);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Slug is already in use by another post";
    }
    $stmt->close();
    
    // Set published_at date if status is published
    $publishedAt = null;
    if ($status === 'published') {
        $publishedAt = date('Y-m-d H:i:s');
        if ($isEdit && $post['status'] === 'published' && !empty($post['published_at'])) {
            $publishedAt = $post['published_at'];
        }
    }
    
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert or update post
            if ($isEdit) {
                $query = "UPDATE blog_posts SET 
                          title = ?, slug = ?, excerpt = ?, content = ?, 
                          featured_image = ?, author_id = ?, status = ?, 
                          is_featured = ?, meta_title = ?, meta_description = ?,
                          published_at = ?
                          WHERE post_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssisssssi", 
                    $title, $slug, $excerpt, $content,
                    $post['featured_image'], $authorId, $status,
                    $isFeatured, $metaTitle, $metaDescription,
                    $publishedAt, $postId);
            } else {
                $query = "INSERT INTO blog_posts 
                          (title, slug, excerpt, content, featured_image, 
                           author_id, status, is_featured, meta_title, meta_description, published_at)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssisssss", 
                    $title, $slug, $excerpt, $content,
                    $post['featured_image'], $authorId, $status,
                    $isFeatured, $metaTitle, $metaDescription,
                    $publishedAt);
            }
            
            $stmt->execute();
            
            if (!$isEdit) {
                $postId = $stmt->insert_id;
            }
            $stmt->close();
            
            // Update categories
            // First delete existing mappings
            $deleteCats = "DELETE FROM blog_post_categories WHERE post_id = ?";
            $stmt = $conn->prepare($deleteCats);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $stmt->close();
            
            // Insert new mappings
            if (!empty($categories)) {
                $insertCat = "INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insertCat);
                
                foreach ($categories as $catId) {
                    $stmt->bind_param("ii", $postId, $catId);
                    $stmt->execute();
                }
                $stmt->close();
            }
            
            // Update tags
            // First delete existing mappings
            $deleteTags = "DELETE FROM blog_post_tags WHERE post_id = ?";
            $stmt = $conn->prepare($deleteTags);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $stmt->close();
            
            // Insert new mappings
            if (!empty($tags)) {
                $insertTag = "INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insertTag);
                
                foreach ($tags as $tagId) {
                    $stmt->bind_param("ii", $postId, $tagId);
                    $stmt->execute();
                }
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success_message'] = "Blog post " . ($isEdit ? "updated" : "created") . " successfully!";
            header("Location: blog-posts.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Handle image upload via AJAX
if (isset($_FILES['featured_image'])) {
    $uploadDir = '../uploads/blog/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $file = $_FILES['featured_image'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error']);
        exit();
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit();
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
        exit();
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_') . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // If editing, delete old image
        if ($isEdit && !empty($post['featured_image'])) {
            $oldImage = $uploadDir . $post['featured_image'];
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
        
        // Update post image in database
        if ($isEdit) {
            $updateQuery = "UPDATE blog_posts SET featured_image = ? WHERE post_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $filename, $postId);
            $stmt->execute();
            $stmt->close();
        }
        
        echo json_encode(['success' => true, 'filename' => $filename]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Blog Post - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'include/admin-header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'include/admin-sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $isEdit ? 'Edit' : 'Add New' ?> Blog Post</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="blog-posts.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Posts
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" id="post-form">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($post['title']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="slug" name="slug" 
                                                   value="<?= htmlspecialchars($post['slug']) ?>" required>
                                            <button class="btn btn-outline-secondary" type="button" id="generate-slug">
                                                <i class="bi bi-arrow-repeat"></i> Generate
                                            </button>
                                        </div>
                                        <small class="text-muted">The slug is used in the URL. Use lowercase letters, numbers, and hyphens only.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt *</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required><?= htmlspecialchars($post['excerpt']) ?></textarea>
                                        <small class="text-muted">A short summary of the post (max 255 characters).</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($post['content']) ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?= htmlspecialchars($post['meta_title']) ?>">
                                        <small class="text-muted">Title for SEO (leave blank to use post title).</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= htmlspecialchars($post['meta_description']) ?></textarea>
                                        <small class="text-muted">Description for SEO (leave blank to use excerpt).</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Publish Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    Publish
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                            <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="author_id" class="form-label">Author</label>
                                        <select class="form-select" id="author_id" name="author_id">
                                            <?php foreach ($authors as $author): ?>
                                                <option value="<?= $author['user_id'] ?>" 
                                                    <?= $post['author_id'] == $author['user_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($author['first_name'] . ' ' . $author['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                               value="1" <?= $post['is_featured'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Featured Post
                                        </label>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-save"></i> <?= $isEdit ? 'Update' : 'Publish' ?> Post
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Featured Image Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    Featured Image
                                </div>
                                <div class="card-body">
                                    <div id="featured-image-container" class="mb-3">
                                        <?php if (!empty($post['featured_image'])): ?>
                                            <img src="../uploads/blog/<?= htmlspecialchars($post['featured_image']) ?>" 
                                                 class="img-fluid rounded" id="current-featured-image">
                                        <?php else: ?>
                                            <div class="text-center py-4 bg-light rounded">
                                                <i class="bi bi-image fs-1 text-muted"></i>
                                                <p class="mt-2 mb-0">No featured image</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <small class="text-muted">Recommended size: 1200x630px (max 5MB)</small>
                                    
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-featured-image">
                                                <i class="bi bi-trash"></i> Remove Image
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Categories Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    Categories
                                </div>
                                <div class="card-body">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="cat-<?= $category['category_id'] ?>" 
                                                   name="categories[]" 
                                                   value="<?= $category['category_id'] ?>"
                                                   <?= in_array($category['category_id'], $selectedCategories) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="cat-<?= $category['category_id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Tags Card -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    Tags
                                </div>
                                <div class="card-body">
                                    <?php foreach ($tags as $tag): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="tag-<?= $tag['tag_id'] ?>" 
                                                   name="tags[]" 
                                                   value="<?= $tag['tag_id'] ?>"
                                                   <?= in_array($tag['tag_id'], $selectedTags) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="tag-<?= $tag['tag_id'] ?>">
                                                <?= htmlspecialchars($tag['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Summernote
            $('#content').summernote({
                height: 400,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture', 'video', 'table']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Generate slug from title
            $('#generate-slug').click(function() {
                const title = $('#title').val();
                if (title) {
                    const slug = title.toLowerCase()
                        .replace(/[^\w\s-]/g, '') // Remove non-word chars
                        .replace(/\s+/g, '-')     // Replace spaces with -
                        .replace(/--+/g, '-')     // Replace multiple - with single -
                        .trim();                  // Trim - from start and end
                    $('#slug').val(slug);
                }
            });

            // Handle featured image upload
            $('#featured_image').change(function() {
                const file = this.files[0];
                if (!file) return;
                
                const formData = new FormData();
                formData.append('featured_image', file);
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Update the image preview
                            $('#featured-image-container').html(
                                `<img src="../uploads/blog/${response.filename}?t=${new Date().getTime()}" 
                                      class="img-fluid rounded" id="current-featured-image">`
                            );
                            
                            // Show remove button
                            if (!$('#remove-featured-image').length) {
                                $('#featured-image-container').after(
                                    `<div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="remove-featured-image">
                                            <i class="bi bi-trash"></i> Remove Image
                                        </button>
                                    </div>`
                                );
                            }
                            
                            // Clear file input
                            $('#featured_image').val('');
                        } else {
                            alert(response.message || 'Error uploading image');
                        }
                    },
                    error: function() {
                        alert('Error uploading image');
                    }
                });
            });

            // Handle remove featured image
            $(document).on('click', '#remove-featured-image', function() {
                if (confirm('Are you sure you want to remove the featured image?')) {
                    $.post('blog-post-remove-image.php', { 
                        post_id: <?= $postId ?>, 
                        filename: '<?= $post['featured_image'] ?>' 
                    }, function(response) {
                        if (response.success) {
                            $('#featured-image-container').html(
                                `<div class="text-center py-4 bg-light rounded">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                    <p class="mt-2 mb-0">No featured image</p>
                                </div>`
                            );
                            $('#remove-featured-image').remove();
                        } else {
                            alert(response.message || 'Error removing image');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>