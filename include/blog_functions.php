<?php
function getFeaturedPost($conn) {
    $query = "SELECT p.*, u.first_name, u.last_name 
              FROM blog_posts p
              JOIN users u ON p.author_id = u.user_id
              WHERE p.is_featured = 1 AND p.status = 'published'
              ORDER BY p.published_at DESC LIMIT 1";
    
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

function getRecentPosts($conn, $limit = 4, $excludeFeatured = true) {
    $posts = [];
    $query = "SELECT p.*, u.first_name, u.last_name 
              FROM blog_posts p
              JOIN users u ON p.author_id = u.user_id
              WHERE p.status = 'published'";
    
    if ($excludeFeatured) {
        $query .= " AND p.is_featured = 0";
    }
    
    $query .= " ORDER BY p.published_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $stmt->close();
    return $posts;
}

function getBlogCategories($conn) {
    $categories = [];
    $query = "SELECT c.*, COUNT(pc.post_id) as post_count
              FROM blog_categories c
              LEFT JOIN blog_post_categories pc ON c.category_id = pc.category_id
              GROUP BY c.category_id
              ORDER BY c.name ASC";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

function getRecentPostsForSidebar($conn, $limit = 3) {
    $posts = [];
    $query = "SELECT p.post_id, p.title, p.featured_image, p.created_at
              FROM blog_posts p
              WHERE p.status = 'published'
              ORDER BY p.published_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $stmt->close();
    return $posts;
}

function getPopularTags($conn, $limit = 6) {
    $tags = [];
    $query = "SELECT t.*, COUNT(pt.post_id) as post_count
              FROM blog_tags t
              LEFT JOIN blog_post_tags pt ON t.tag_id = pt.tag_id
              GROUP BY t.tag_id
              ORDER BY post_count DESC, t.name ASC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    
    $stmt->close();
    return $tags;
}

function getPostCommentsCount($conn, $postId) {
    $query = "SELECT COUNT(*) as comment_count 
              FROM blog_comments 
              WHERE post_id = ? AND status = 'approved'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['comment_count'];
}