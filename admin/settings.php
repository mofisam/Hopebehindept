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

// Define settings structure
$settingsStructure = [
    'site' => [
        'title' => 'Site Settings',
        'fields' => [
            'site_title' => ['label' => 'Site Title', 'type' => 'text', 'required' => true],
            'site_tagline' => ['label' => 'Tagline', 'type' => 'text'],
            'site_email' => ['label' => 'Admin Email', 'type' => 'email', 'required' => true],
            'timezone' => ['label' => 'Timezone', 'type' => 'select', 'options' => DateTimeZone::listIdentifiers()],
            'date_format' => ['label' => 'Date Format', 'type' => 'text', 'default' => 'F j, Y'],
            'posts_per_page' => ['label' => 'Posts Per Page', 'type' => 'number', 'default' => 10]
        ]
    ],
    'social' => [
        'title' => 'Social Media',
        'fields' => [
            'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url'],
            'twitter_url' => ['label' => 'Twitter URL', 'type' => 'url'],
            'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url'],
            'linkedin_url' => ['label' => 'LinkedIn URL', 'type' => 'url']
        ]
    ],
    'seo' => [
        'title' => 'SEO Settings',
        'fields' => [
            'meta_description' => ['label' => 'Default Meta Description', 'type' => 'textarea'],
            'meta_keywords' => ['label' => 'Default Keywords', 'type' => 'text'],
            'google_analytics' => ['label' => 'Google Analytics ID', 'type' => 'text']
        ]
    ],
    'maintenance' => [
        'title' => 'Maintenance Mode',
        'fields' => [
            'maintenance_mode' => ['label' => 'Enable Maintenance Mode', 'type' => 'checkbox'],
            'maintenance_message' => ['label' => 'Maintenance Message', 'type' => 'textarea']
        ]
    ]
];

// Get current settings from database
$currentSettings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate and prepare settings
    $settingsToSave = [];
    foreach ($settingsStructure as $section => $sectionData) {
        foreach ($sectionData['fields'] as $key => $field) {
            $value = $_POST[$key] ?? '';
            
            // Handle different field types
            if ($field['type'] === 'checkbox') {
                $value = isset($_POST[$key]) ? '1' : '0';
            } elseif ($field['type'] === 'number') {
                $value = (int)$value;
            } else {
                $value = trim($value);
            }
            
            // Validate required fields
            if (!empty($field['required']) && empty($value)) {
                $errors[] = "{$field['label']} is required";
            }
            
            $settingsToSave[$key] = $value;
        }
    }
    
    // Save to database if no errors
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // First delete all existing settings
            $conn->query("DELETE FROM site_settings");
            
            // Insert new settings
            $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            
            foreach ($settingsToSave as $key => $value) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Settings updated successfully!";
            header("Location: settings.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Set page title
$pageTitle = "System Settings";

// Include header
include 'include/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'include/admin-sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="submit" form="settings-form" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
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

            <form id="settings-form" method="post">
                <div class="row">
                    <!-- Tabs Navigation -->
                    <div class="col-md-3 mb-4">
                        <div class="list-group">
                            <?php foreach ($settingsStructure as $section => $sectionData): ?>
                                <a class="list-group-item list-group-item-action" 
                                   href="#<?= $section ?>" 
                                   data-bs-toggle="tab">
                                    <?= $sectionData['title'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Tabs Content -->
                    <div class="col-md-9">
                        <div class="tab-content">
                            <?php foreach ($settingsStructure as $section => $sectionData): ?>
                                <div class="tab-pane fade" id="<?= $section ?>">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><?= $sectionData['title'] ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($sectionData['fields'] as $key => $field): ?>
                                                <div class="mb-3">
                                                    <?php if ($field['type'] === 'checkbox'): ?>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   id="<?= $key ?>" 
                                                                   name="<?= $key ?>"
                                                                   value="1"
                                                                   <?= isset($currentSettings[$key]) && $currentSettings[$key] === '1' ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="<?= $key ?>">
                                                                <?= $field['label'] ?>
                                                            </label>
                                                        </div>
                                                    <?php elseif ($field['type'] === 'textarea'): ?>
                                                        <label for="<?= $key ?>" class="form-label">
                                                            <?= $field['label'] ?>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="<?= $key ?>" 
                                                                  name="<?= $key ?>" 
                                                                  rows="3"><?= htmlspecialchars($currentSettings[$key] ?? $field['default'] ?? '') ?></textarea>
                                                    <?php elseif ($field['type'] === 'select'): ?>
                                                        <label for="<?= $key ?>" class="form-label">
                                                            <?= $field['label'] ?>
                                                        </label>
                                                        <select class="form-select" 
                                                                id="<?= $key ?>" 
                                                                name="<?= $key ?>">
                                                            <?php foreach ($field['options'] as $option): ?>
                                                                <option value="<?= $option ?>" 
                                                                    <?= (isset($currentSettings[$key]) && $currentSettings[$key] === $option) ? 'selected' : '' ?>>
                                                                    <?= $option ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <label for="<?= $key ?>" class="form-label">
                                                            <?= $field['label'] ?>
                                                            <?php if (!empty($field['required'])): ?>
                                                                <span class="text-danger">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                        <input type="<?= $field['type'] ?>" 
                                                               class="form-control" 
                                                               id="<?= $key ?>" 
                                                               name="<?= $key ?>" 
                                                               value="<?= htmlspecialchars($currentSettings[$key] ?? $field['default'] ?? '') ?>"
                                                               <?php if (!empty($field['required'])) echo 'required'; ?>>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include 'include/admin-footer.php'; ?>

<script>
    // Activate first tab by default
    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('.list-group-item-action');
        if (firstTab) {
            const tab = new bootstrap.Tab(firstTab);
            tab.show();
        }
    });
</script>