<?php
include 'include/header.php';
?>

<!-- Hero Section -->
<section class="hero-section py-5" >
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 ">
        <h1 class="display-4 fw-bold text-success mb-4">Making a Financial Impact Together</h1>
        <p class="lead mb-4 text-success" style="text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Join us in redistributing financial knowledge and support to underserved communities across Africa.</p>
        <a href="#programs" class="btn btn-success btn-lg px-4 bg-success py-2 text-white fw-bold shadow">Explore programs</a>
      </div>
      <div class="col-md-6 d-none d-md-block">
        <div class="row g-2">
          <div class="col-6">
            <img src="assets/images/img.jpg" alt="Medical volunteers packing supplies" 
                 class="img-fluid rounded shadow-lg" loading="lazy" width="600" height="400"
                 style="border: 3px solid rgba(255,255,255,0.8);">
            <img src="assets/images/img.jpg" alt="Delivering medical supplies" 
                 class="img-fluid rounded mt-2 shadow-lg" loading="lazy" width="600" height="400"
                 style="border: 3px solid rgba(255,255,255,0.8);">
          </div>
          <div class="col-6">
            <img src="assets/images/img.jpg" alt="Doctor examining patient" 
                 class="img-fluid rounded mb-2 shadow-lg" loading="lazy" width="600" height="190"
                 style="border: 3px solid rgba(255,255,255,0.8);">
            <img src="assets/images/img.jpg" alt="Community health training" 
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
        <p class="lead">Through collaboration and dedication, we have created meaningful change in communities around the world.</p>
      </div>
      <div class="col-lg-6">
        <div class="row">
          <!-- Stat 1 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-primary counter" data-target="120">0</h3>
                </div>
              </div>
              <p class="text-center">Communities Impacted</p>
            </div>
          </div>
          
          <!-- Stat 2 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-success counter" data-target="85">0</h3>
                </div>
              </div>
              <p class="text-center">programs Created</p>
            </div>
          </div>
        
          <!-- Stat 3 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-info counter" data-target="3">0</h3>
                </div>
              </div>
              <p class="text-center">Total Amount (₦M)</p>
            </div>
          </div>
          
          <!-- Stat 4 -->
          <div class="col-6 mb-4">
            <div class="d-flex flex-column align-items-center">
              <div class="position-relative mb-3">
                <div class="stat-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                  <h3 class="display-5 fw-bold mb-0 text-warning counter" data-target="0">0</h3>
                </div>
              </div>
              <p class="text-center">Funding Success Rate</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  /* Custom CSS for the animated stats */
  .stat-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    transition: all 0.3s ease;
  }
  
  .stat-circle:hover {
    transform: scale(1.05);
  }
  
  /* Animation for the counter */
  @keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .counter {
    animation: countUp 1s ease-out forwards;
  }
</style>

<script>
  // Counter animation script
  document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // The lower the faster
    
    counters.forEach(counter => {
      const target = +counter.getAttribute('data-target');
      const count = +counter.innerText;
      const increment = target / speed;
      
      if (count < target) {
        const updateCount = () => {
          const current = +counter.innerText;
          const newCount = Math.ceil(current + increment);
          
          if (current < target) {
            counter.innerText = newCount;
            setTimeout(updateCount, 1);
          } else {
            counter.innerText = target;
            // Add '+' for communities impacted
            if (counter.classList.contains('text-primary')) {
              counter.innerText = target + '+';
            }
            // Add '%' for success rate
            if (counter.classList.contains('text-warning')) {
              counter.innerText = target + '%';
            }
            // Add 'M' for millions
            if (counter.classList.contains('text-info')) {
              counter.innerText = '₦' + target + 'M';
            }
          }
        };
        
        // Start counting when element is in viewport
        const observer = new IntersectionObserver((entries) => {
          if (entries[0].isIntersecting) {
            updateCount();
            observer.unobserve(counter);
          }
        });
        
        observer.observe(counter);
      }
    });
  });
</script>

<!-- Join Us Section -->
<section class="container text-center py-5 my-4">
  <h2 class="display-6 fw-bold">Join Us in Making a Difference</h2>
  <p class="lead text-muted mb-4">Become part of a movement that transforms lives through health and hope.</p>
  <a href="#programs" class="btn btn-outline-success btn-lg px-4 py-2">Explore programs</a>
</section>

<!-- Who We Are -->
<section class="bg-light py-5">
  <div class="container">
    <h2 class="text-center mb-5 display-5 fw-bold">Who We Are</h2>
    <div class="row text-center">
      <div class="col-md-4 mb-4">
        <img src="assets/images/img.jpg" alt="Our Purpose" 
             class="img-fluid mb-3 rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
        <h5 class="fw-bold">Our Purpose</h5>
        <p>awaHealth exists to empower underserved communities by connecting them with the health resources they deserve. We believe that good health is a shared right, and we’re here to make it accessible for all.</p>
      </div>
      <div class="col-md-4 mb-4">
        <img src="assets/images/img.jpg" alt="Our Identity" 
             class="img-fluid mb-3 rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
        <h5 class="fw-bold">Our Identity</h5>
        <p>awaHealth exists to empower underserved communities by connecting them with the health resources they deserve. We believe that good health is a shared right, and we’re here to make it accessible for all.</p>
      </div>
      <div class="col-md-4 mb-4">
        <img src="assets/images/img.jpg" alt="Our Vision" 
             class="img-fluid mb-3 rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
        <h5 class="fw-bold">Our Vision</h5>
        <p>awaHealth exists to empower underserved communities by connecting them with the health resources they deserve. We believe that good health is a shared right, and we’re here to make it accessible for all.</p>
      </div>
    </div>
  </div>
</section>

<!-- Support a Project -->
<section id="programs" class="container py-5">
  <h2 class="mb-5 text-center display-5 fw-bold">Support a Project</h2>
  <div class="row g-4">

    <!-- Project Card 1 -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <img src="assets/images/img.jpg" class="card-img-top" alt="Medical Supplies for Rural Clinics" loading="lazy">
        <div class="card-body">
          <h5 class="card-title fw-bold">Medical Supplies for Rural Clinics</h5>
          <p class="card-text">Providing essential medical equipment to clinics in remote areas to improve healthcare access.</p>
          <div class="progress mt-3 mb-2" style="height: 8px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>₦35,000 raised</span>
            <span>70%</span>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0">
          <a href="#" class="btn btn-success w-100">Donate Now</a>
        </div>
      </div>
    </div>

    <!-- Project Card 2 -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <img src="assets/images/img.jpg" class="card-img-top" alt="Health Education Outreach" loading="lazy">
        <div class="card-body">
          <h5 class="card-title fw-bold">Health Education Outreach</h5>
          <p class="card-text">Empowering communities with health knowledge and preventive care practices.</p>
          <div class="progress mt-3 mb-2" style="height: 8px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>₦22,500 raised</span>
            <span>75%</span>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0">
          <a href="#" class="btn btn-success w-100">Donate Now</a>
        </div>
      </div>
    </div>

    <!-- Project Card 3 -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <img src="assets/images/img.jpg" class="card-img-top" alt="Emergency Medical Kits" loading="lazy">
        <div class="card-body">
          <h5 class="card-title fw-bold">Emergency Medical Kits</h5>
          <p class="card-text">Supplying emergency kits to disaster-prone regions for timely response and care.</p>
          <div class="progress mt-3 mb-2" style="height: 8px;">
            <div class="progress-bar bg-warning" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>₦28,000 raised</span>
            <span>70%</span>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0">
          <a href="#" class="btn btn-success w-100">Donate Now</a>
        </div>
      </div>
    </div>

  </div>
  <div class="text-center mt-5">
    <a href="#" class="btn btn-outline-success btn-lg px-4 py-2">View All programs</a>
  </div>
</section>

<?php
include 'include/footer.php';
?>