<?php 
include 'include/header.php';
?>

<!-- Blog Hero Section -->
<section class="blog-hero py-5" style="background: linear-gradient(135deg, #ffb420 0%, #ff8c00 50%, #ff6b00 100%);">
  <div class="container py-5">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold text-white mb-4">Our Blog</h1>
        <p class="lead text-white mb-4">Insights, stories and updates on our mission to improve global  access</p>
        <div class="search-bar mx-auto" style="max-width: 500px;">
          <form class="d-flex">
            <input class="form-control me-2" type="search" placeholder="Search articles..." aria-label="Search">
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
      <div class="card mb-5 border-0 shadow-lg">
        <div class="position-relative">
          <img src="images/blog-featured.jpg" class="card-img-top" alt="Featured Blog Post" style="height: 400px; object-fit: cover;">
          <div class="position-absolute top-0 start-0 bg-primary text-white px-3 py-2">
            Featured
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex mb-3">
            <span class="text-muted me-3"><i class="far fa-calendar me-2"></i>June 15, 2023</span>
            <span class="text-muted"><i class="far fa-user me-2"></i>By Admin</span>
          </div>
          <h2 class="card-title mb-3">How We’re Transforming Financial Education and Debt Relief in Underserved Communities</h2>
          <p class="card-text">Discover how our innovative education network is empowering underserved communities with the financial tools they need. This comprehensive look at our latest initiative shows the real-world impact of your support in helping people achieve debt freedom and long-term financial stability.</p>
          <a href="blog-single.php" class="btn btn-primary px-4">Read More</a>
        </div>
      </div>

      <!-- Blog Posts Grid -->
      <div class="row g-4">
        <!-- Blog Post 1 -->
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm">
            <img src="images/blog1.jpg" class="card-img-top" alt="Blog Post 1" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <div class="d-flex mb-2">
                <span class="text-muted small me-3"><i class="far fa-calendar me-1"></i>June 10, 2023</span>
                <span class="text-muted small"><i class="far fa-comment me-1"></i>5 Comments</span>
              </div>
              <h3 class="h5 card-title">Success Stories: Clinic in Kenya Receives Vital Equipment</h3>
              <p class="card-text">Read how our partnership with local health workers is making a difference in one Kenyan community.</p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="blog-single.php" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
          </div>
        </div>

        <!-- Blog Post 2 -->
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm">
            <img src="images/blog2.jpg" class="card-img-top" alt="Blog Post 2" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <div class="d-flex mb-2">
                <span class="text-muted small me-3"><i class="far fa-calendar me-1"></i>May 28, 2023</span>
                <span class="text-muted small"><i class="far fa-comment me-1"></i>3 Comments</span>
              </div>
              <h3 class="h5 card-title">The Environmental Impact of Medical Waste Reduction</h3>
              <p class="card-text">How redistributing surplus supplies helps both people and the planet through sustainable practices.</p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="blog-single.php" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
          </div>
        </div>

        <!-- Blog Post 3 -->
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm">
            <img src="images/blog3.jpg" class="card-img-top" alt="Blog Post 3" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <div class="d-flex mb-2">
                <span class="text-muted small me-3"><i class="far fa-calendar me-1"></i>May 15, 2023</span>
                <span class="text-muted small"><i class="far fa-comment me-1"></i>8 Comments</span>
              </div>
              <h3 class="h5 card-title">Volunteer Spotlight: Meet Our Distribution Team</h3>
              <p class="card-text">The incredible people who make our work possible share their experiences and motivations.</p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="blog-single.php" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
          </div>
        </div>

        <!-- Blog Post 4 -->
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm">
            <img src="images/blog4.jpg" class="card-img-top" alt="Blog Post 4" style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <div class="d-flex mb-2">
                <span class="text-muted small me-3"><i class="far fa-calendar me-1"></i>April 30, 2023</span>
                <span class="text-muted small"><i class="far fa-comment me-1"></i>2 Comments</span>
              </div>
              <h3 class="h5 card-title">How Corporate Partnerships Amplify Our Impact</h3>
              <p class="card-text">Learn how businesses are joining our mission and how your company can get involved.</p>
            </div>
            <div class="card-footer bg-transparent border-0">
              <a href="blog-single.php" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
          </div>
        </div>
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
            <h4 class="card-title mb-3" style="color: #ffb420;">About Our Blog</h4>
            <p class="card-text">Stay updated with our latest initiatives, success stories, and insights into global healthcare challenges and solutions.</p>
            <div class="d-flex">
              <a href="#" class="btn btn-sm btn-outline-primary me-2">Learn More</a>
              <a href="#" class="btn btn-sm btn-primary" style="background-color: #ffb420; border-color: #ffb420;">Subscribe</a>
            </div>
          </div>
        </div>

        <!-- Categories Widget -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3" style="color: #ffb420;">Categories</h4>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="#" class="text-decoration-none">Success Stories</a>
                <span class="badge rounded-pill bg-primary">14</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="#" class="text-decoration-none">Medical Missions</a>
                <span class="badge rounded-pill bg-primary">8</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="#" class="text-decoration-none">Volunteer Spotlights</a>
                <span class="badge rounded-pill bg-primary">5</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="#" class="text-decoration-none">Environmental Impact</a>
                <span class="badge rounded-pill bg-primary">3</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <a href="#" class="text-decoration-none">Partnership News</a>
                <span class="badge rounded-pill bg-primary">7</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Recent Posts Widget -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3" style="color: #ffb420;">Recent Posts</h4>
            <div class="mb-3 d-flex">
              <img src="images/blog-thumb1.jpg" alt="Post thumb" class="rounded me-3" width="80" height="60" style="object-fit: cover;">
              <div>
                <h6 class="mb-1"><a href="#" class="text-decoration-none">New Distribution Center Opens in Ghana</a></h6>
                <small class="text-muted">June 5, 2023</small>
              </div>
            </div>
            <div class="mb-3 d-flex">
              <img src="images/blog-thumb2.jpg" alt="Post thumb" class="rounded me-3" width="80" height="60" style="object-fit: cover;">
              <div>
                <h6 class="mb-1"><a href="#" class="text-decoration-none">Annual Impact Report 2023</a></h6>
                <small class="text-muted">May 20, 2023</small>
              </div>
            </div>
            <div class="d-flex">
              <img src="images/blog-thumb3.jpg" alt="Post thumb" class="rounded me-3" width="80" height="60" style="object-fit: cover;">
              <div>
                <h6 class="mb-1"><a href="#" class="text-decoration-none">How to Organize a Supply Drive</a></h6>
                <small class="text-muted">April 15, 2023</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Tags Widget -->
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3" style="color: #ffb420;">Popular Tags</h4>
            <div class="tags">
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#Healthcare</a>
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#Africa</a>
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#Sustainability</a>
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#MedicalSupplies</a>
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#Volunteer</a>
              <a href="#" class="btn btn-sm btn-outline-secondary mb-2 me-1">#GlobalHealth</a>
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

<!-- Additional Styles -->
<style>
  .blog-hero {
    position: relative;
    overflow: hidden;
  }
  
  .blog-hero::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 10px 10px;
    z-index: 0;
  }
  
  .blog-hero .container {
    position: relative;
    z-index: 1;
  }
  
  .card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
  }
  
  .page-item.active .page-link {
    background-color: #ffb420;
    border-color: #ffb420;
  }
  
  .page-link {
    color: #ffb420;
  }
  
  .page-link:hover {
    color: #d8960d;
  }
</style>