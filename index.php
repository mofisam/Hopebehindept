<?php
// Start session
if (session_status() === PHP_SESSION_NONE) session_start();
// Include database configuration
require_once __DIR__ . '/config/db.php';
include 'include/header.php';
require_once 'include/functions.php';

// Get data from database
$programs = getPrograms($conn);
$stats = getProgramStats($conn);
$successStories = getSuccessStories($conn);
?>

<!-- Hero Section -->
<section class="hero-section py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h1 class="display-4 fw-bold text-white mb-4">Making a Financial Impact Together</h1>
        <p class="lead mb-4 text-white" style="text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Join us in redistributing financial knowledge and support to underserved communities across Africa.</p>
        
        <!-- Bullet points from your content -->
        <ul class="list-unstyled mb-4">
          <li class="mb-2 text-white"><i class="fas fa-check-circle text-success me-2"></i> Youth learning budgeting skills</li>
          <li class="mb-2 text-white"><i class="fas fa-check-circle text-success me-2"></i> Debt recovery support session</li>
          <li class="mb-2 text-white"><i class="fas fa-check-circle text-success me-2"></i> Behavioral change outreach</li>
          <li class="mb-2 text-white"><i class="fas fa-check-circle text-success me-2"></i> Financial literacy training in schools</li>
        </ul>
        
        <a href="#programs" class="btn btn-success btn-lg px-4 bg-success py-2 text-white fw-bold shadow">Explore programs</a>
      </div>
      <div class="col-md-6 d-none d-md-block">
        <div class="row g-2">
          <div class="col-6">
            <img src="assets/images/img.jpg" alt="Youth financial literacy training" 
                 class="img-fluid rounded shadow-lg" loading="lazy" width="600" height="400"
                 style="border: 3px solid rgba(255,255,255,0.8);">
            <img src="assets/images/img.jpg" alt="Community financial workshop" 
                 class="img-fluid rounded mt-2 shadow-lg" loading="lazy" width="600" height="400"
                 style="border: 3px solid rgba(255,255,255,0.8);">
          </div>
          <div class="col-6">
            <img src="assets/images/img.jpg" alt="Debt recovery session" 
                 class="img-fluid rounded mb-2 shadow-lg" loading="lazy" width="600" height="190"
                 style="border: 3px solid rgba(255,255,255,0.8);">
            <img src="assets/images/img.jpg" alt="School financial education" 
                 class="img-fluid rounded shadow-lg" loading="lazy" width="600" height="190"
                 style="border: 3px solid rgba(255,255,255,0.8);">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Impact & Progress -->
<section class="bg-light py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <h2 class="display-5 fw-bold">Our Impact & Progress</h2>
        <p class="lead">Through partnerships, education, and community-driven efforts, we are rewriting the financial stories of individuals and communities across Nigeria.</p>
      </div>
      <div class="col-lg-6">
        <div class="row">
          <!-- Stat 1 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-primary counter" data-target="<?php echo $stats['total_people_helped'] ?? 1; ?>">0</h3>
                </div>
              </div>
              <p class="text-center">People Helped</p>
            </div>
          </div>
          
          <!-- Stat 2 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-success counter" data-target="<?php echo $stats['total_workshops'] ?? 1; ?>">0</h3>
                </div>
              </div>
              <p class="text-center">Workshops Conducted</p>
            </div>
          </div>
        
          <!-- Stat 3 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-info">₦<?php echo number_format($stats['total_amount_relieved'] ?? 52000); ?>k</h3>
                </div>
              </div>
              <p class="text-center">Financial Support Distributed</p>
            </div>
          </div>
          
          <!-- Stat 4 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-warning"><?php echo round($stats['avg_success_rate'] ?? 90); ?>%</h3>
                </div>
              </div>
              <p class="text-center">Success Rate</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Support a Project -->
<section id="programs" class="container py-5">
  <h2 class="mb-5 text-center display-5 fw-bold">Support a Program</h2>
  <div class="row g-4">
    <?php foreach ($programs as $program): ?>
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <img src="assets/images/<?php echo htmlspecialchars($program['featured_image'] ?? 'img.jpg'); ?>" 
             class="card-img-top" alt="<?php echo htmlspecialchars($program['title']); ?>" loading="lazy">
        <div class="card-body">
          <h5 class="card-title fw-bold"><?php echo htmlspecialchars($program['title']); ?></h5>
          <p class="card-text"><?php echo htmlspecialchars($program['description']); ?></p>
          <div class="progress mt-3 mb-2" style="height: 8px;">
            <div class="progress-bar <?php echo ($program['progress'] >= 100) ? 'bg-success' : 'bg-warning'; ?>" 
                 role="progressbar" style="width: <?php echo $program['progress']; ?>%;" 
                 aria-valuenow="<?php echo $program['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>₦<?php echo number_format($program['amount_raised']); ?> raised</span>
            <span><?php echo $program['progress']; ?>% funded</span>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0">
          <a href="program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-success w-100">
            <?php echo ($program['progress'] >= 100) ? 'View Program' : 'Donate Now'; ?>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-5">
    <a href="programs.php" class="btn btn-outline-success btn-lg px-4 py-2">View All Programs</a>
  </div>
</section>

<!-- Success Stories -->
<section class="bg-light py-5">
  <div class="container py-4">
    <h2 class="display-5 fw-bold text-center mb-5">Success Stories</h2>
    <div class="row g-4">
      <?php foreach ($successStories as $story): ?>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <img src="assets/images/<?php echo htmlspecialchars($story['author_image'] ?? 'img.jpg'); ?>" 
                 class="rounded-circle mb-3" width="100" height="100" alt="<?php echo htmlspecialchars($story['author_name']); ?>" 
                 style="object-fit: cover;">
            <h4 class="h5"><?php echo htmlspecialchars($story['author_name']); ?></h4>
            <p class="mb-3">"<?php echo htmlspecialchars($story['content']); ?>"</p>
            <div class="text-warning mb-2">
              <?php 
              $fullStars = floor($story['rating']);
              $halfStar = ($story['rating'] - $fullStars) >= 0.5;
              
              for ($i = 0; $i < $fullStars; $i++) {
                  echo '<i class="fas fa-star"></i>';
              }
              if ($halfStar) {
                  echo '<i class="fas fa-star-half-alt"></i>';
              }
              ?>
            </div>
            <small class="text-muted"><?php echo htmlspecialchars($story['title']); ?></small>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
include 'include/footer.php';
?>