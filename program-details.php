<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: programs.php");
    exit();
}

$programId = (int)$_GET['id'];
$program = getProgramById($conn, $programId);

if (!$program) {
    header("Location: programs.php");
    exit();
}

// Add to functions.php
function getProgramById($conn, $id) {
    $query = "SELECT * FROM programs WHERE program_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program = $result->fetch_assoc();
    $stmt->close();
    return $program;
}
include 'include/header.php';
?>

<section class="py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($program['title']) ?></h1>
        <img src="/images/<?= htmlspecialchars($program['featured_image'] ?? 'default-program.jpg') ?>" 
             class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($program['title']) ?>">
        
        <div class="mb-4">
          <?= $program['description'] ?>
        </div>
        
        <div class="card mb-4">
          <div class="card-body">
            <h3 class="h4">Program Details</h3>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between">
                <span>Status:</span>
                <span class="badge bg-<?= $program['status'] == 'active' ? 'success' : 'warning' ?>">
                  <?= ucfirst($program['status']) ?>
                </span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>Goal Amount:</span>
                <span>₦<?= number_format($program['funding_goal']) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>Amount Raised:</span>
                <span>₦<?= number_format($program['amount_raised']) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>Progress:</span>
                <span><?= $program['progress'] ?>%</span>
              </li>
              <?php if ($program['start_date']): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span>Start Date:</span>
                <span><?= date('F j, Y', strtotime($program['start_date'])) ?></span>
              </li>
              <?php endif; ?>
              <?php if ($program['end_date']): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span>End Date:</span>
                <span><?= date('F j, Y', strtotime($program['end_date'])) ?></span>
              </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="h4 mb-4">Support This Program</h3>
            
            <?php if ($program['progress'] >= 100): ?>
              <div class="alert alert-success">
                This program has been fully funded! Thank you for your support.
              </div>
              <a href="programs.php" class="btn btn-success w-100">Explore Other Programs</a>
            <?php else: ?>
              <form action="process-donation.php" method="POST">
                <input type="hidden" name="program_id" value="<?= $program['program_id'] ?>">
                
                <div class="mb-3">
                  <label for="amount" class="form-label">Donation Amount (₦)</label>
                  <input type="number" class="form-control" id="amount" name="amount" min="100" required>
                </div>
                
                <button type="submit" class="btn btn-success w-100">Donate Now</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
include 'include/footer.php';
?>