<?php
// Start session
session_start();
// Include database configuration
require_once __DIR__ . '/config/db.php';

include 'include/header.php';
// Get data from database
$featuredPrograms = getFeaturedPrograms($conn);
$successStories = getSuccessStories($conn);

?>

<!-- programs Hero Section -->
<section class="programs-hero py-5" style="background: linear-gradient(180deg, #27a263 0%, #27a263 60%, #feb21e 100%);">
  <div class="container py-5">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold text-white mb-4">Financial Freedom Initiatives</h1>
        <p class="lead text-white mb-4">Empowering individuals and communities through debt relief programs and financial education</p>
      </div>
    </div>
  </div>
</section>

<!-- Impact Stats -->
<section class="bg-light py-5">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-4">
        <div class="stat-circle bg-primary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3">
          <h3 class="display-5 fw-bold mb-0 text-primary counter" data-target="<?= $stats['people_debt_free'] ?>">0</h3>
        </div>
        <p>People Debt-Free</p>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3">
          <h3 class="display-5 fw-bold mb-0 text-success counter" data-target="<?= $stats['workshops_conducted'] ?>">0</h3>
        </div>
        <p>Workshops Conducted</p>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-circle bg-info bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3">
          <h3 class="display-5 fw-bold mb-0 text-info counter" data-target="<?= floor($stats['amount_relieved'] / 1000000) ?>">0</h3>
        </div>
        <p>Million Naira Relieved</p>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-circle bg-warning bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3">
          <h3 class="display-5 fw-bold mb-0 text-warning counter" data-target="<?= $stats['success_rate'] ?>">0</h3>
        </div>
        <p>Success Rate</p>
      </div>
    </div>
  </div>
</section>

<!-- programs Grid -->
<section id="programs" class="container py-5">
  <div class="row mb-5">
    <div class="col-lg-6">
      <h2 class="display-5 fw-bold">Our Financial Freedom Programs</h2>
    </div>
    <div class="col-lg-6 text-lg-end">
      <a href="all-programs.php" class="btn btn-outline-success">View All Initiatives</a>
    </div>
  </div>

  <div class="row g-4">
    <?php foreach ($featuredPrograms as $program): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-0">
        <img src="/images/<?= htmlspecialchars($program['featured_image'] ?? 'default-program.jpg') ?>" 
             class="card-img-top" alt="<?= htmlspecialchars($program['title']) ?>" loading="lazy">
        <div class="card-body">
          <div class="d-flex mb-3">
            <span class="badge bg-<?= $program['status'] == 'active' ? 'success' : 'warning' ?> me-2">
              <?= ucfirst($program['status']) ?>
            </span>
            <span class="text-muted"><i class="far fa-calendar me-1"></i>
              <?= $program['status'] == 'upcoming' ? 'Starting Soon' : 'Ongoing' ?>
            </span>
          </div>
          <h3 class="h4 card-title"><?= htmlspecialchars($program['title']) ?></h3>
          <p class="card-text"><?= htmlspecialchars($program['excerpt']) ?></p>
          <div class="progress mt-3 mb-2" style="height: 8px;">
            <div class="progress-bar bg-<?= $program['progress'] >= 100 ? 'success' : 'warning' ?>" 
                 role="progressbar" style="width: <?= $program['progress'] ?>%;" 
                 aria-valuenow="<?= $program['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>₦<?= number_format($program['amount_raised']) ?> raised</span>
            <span><?= $program['progress'] ?>% funded</span>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0">
          <a href="program-details.php?id=<?= $program['program_id'] ?>" class="btn btn-success w-100">
            <?= $program['progress'] >= 100 ? 'View Program' : 'Support This Program' ?>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
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
            <img src="/images/<?= htmlspecialchars($story['author_image'] ?? 'default-avatar.jpg') ?>" 
                 class="rounded-circle mb-3" width="100" height="100" alt="<?= htmlspecialchars($story['author_name']) ?>" 
                 style="object-fit: cover;">
            <h4 class="h5"><?= htmlspecialchars($story['author_name']) ?></h4>
            <p class="mb-3">"<?= htmlspecialchars($story['content']) ?>"</p>
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
            <small class="text-muted"><?= htmlspecialchars($story['title']) ?></small>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Call to Action -->
<section class="container py-5 my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8 text-center">
      <h2 class="display-6 fw-bold mb-4">Ready to Start Your Financial Freedom Journey?</h2>
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="apply.php" class="btn btn-success btn-lg px-4">Apply for Assistance</a>
        <a href="workshops.php" class="btn btn-outline-success btn-lg px-4">Attend a Workshop</a>
      </div>
    </div>
  </div>
</section>

<?php
include 'include/footer.php';
?>