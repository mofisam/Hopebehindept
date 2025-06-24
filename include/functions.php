<?php
// include/functions.php
function get_setting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['setting_value'] : $default;
}

function getPrograms($conn, $limit = 3) {
    $programs = [];
    $query = "SELECT * FROM programs WHERE status = 'active' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    $stmt->close();
    return $programs;
}

function getFeaturedPrograms($conn, $limit = 3) {
    $programs = [];
    $query = "SELECT * FROM programs WHERE status IN ('active', 'upcoming') ORDER BY is_featured DESC, created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }

    $stmt->close();
    return $programs;
}

function getProgramStats($conn) {
    $stats = [
        'people_debt_free' => 0,
        'workshops_conducted' => 0,
        'amount_relieved' => 0,
        'success_rate' => 0
    ];
    
    $query = "SELECT 
                SUM(people_helped) AS people_debt_free,
                SUM(workshops_conducted) AS workshops_conducted,
                SUM(amount_relieved) AS amount_relieved,
                AVG(success_rate) AS success_rate
              FROM program_stats";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats = array_merge($stats, $row);
    }
    
    return $stats;
}

function getSuccessStories($conn, $limit = 3) {
    $stories = [];
    $query = "SELECT * FROM success_stories WHERE is_featured = 1 ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }

    $stmt->close();
    return $stories;
}
