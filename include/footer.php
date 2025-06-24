<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container">
    <div class="row">
      <!-- Logo and About -->
      <div class="col-lg-4 mb-4">
        <a class="navbar-brand d-block mb-3" href="#">
          <img src="assets/images/logo.jpg" alt="Company Logo" >
        </a>
        <p class="mb-3">Redistributing financial knowledge and resources to underserved communities, creating sustainable pathways to debt freedom and economic empowerment.</p>
        <div class="social-icons">
          <?php if ($fb = get_setting('facebook_url')): ?>
              <a href="<?= htmlspecialchars($fb) ?>" class="text-white me-3" target="_blank"><i class="fab fa-facebook"></i></a>
          <?php endif; ?>
          <?php if ($tw = get_setting('twitter_url')): ?>
              <a href="<?= htmlspecialchars($tw) ?>" class="text-white me-3" target="_blank"><i class="fab fa-twitter"></i></a>
          <?php endif; ?>
          <?php if ($tw = get_setting('twitter_url')): ?>
              <a href="<?= htmlspecialchars($tw) ?>" class="text-white me-3" target="_blank"><i class="fab fa-instagram"></i></a>
          <?php endif; ?>
          <?php if ($tw = get_setting('twitter_url')): ?>
              <a href="<?= htmlspecialchars($tw) ?>" class="text-white me-3" target="_blank"><i class="fab fa-linkedin-in"></i></a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-4 mb-4">
        <h5 class="text-uppercase mb-4 text-success">Quick Links</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="#" class="text-white text-decoration-none">Home</a></li>
          <li class="mb-2"><a href="#programs" class="text-white text-decoration-none">programs</a></li>
          <li class="mb-2"><a href="#" class="text-white text-decoration-none">Blog</a></li>
          <li class="mb-2"><a href="#" class="text-white text-decoration-none">About Us</a></li>
          <li class="mb-2"><a href="#" class="text-white text-decoration-none">Get Involved</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-3 col-md-4 mb-4">
        <h5 class="text-uppercase mb-4 text-success">Contact Us</h5>
        <ul class="list-unstyled">
          <li class="mb-3 d-flex">
            <i class="fas fa-map-marker-alt me-3 mt-1 text-success"></i>
            <span>Nigeria</span>
          </li>
          <li class="mb-3 d-flex">
            <i class="fas fa-phone me-3 mt-1 text-success"></i>
            <span>(234) 800-000-0000</span>
          </li>
          <li class="mb-3 d-flex">
            <i class="fas fa-envelope me-3 mt-1 text-success"></i>
            <span>info@hopebehindept.org</span>
          </li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="col-lg-3 col-md-4 mb-4">
        <h5 class="text-uppercase mb-4 text-success">Newsletter</h5>
        <p>Subscribe to our newsletter for updates on our programs and initiatives.</p>
        <form class="mb-3">
          <div class="input-group">
            <input type="email" class="form-control" placeholder="Your email" aria-label="Your email">
            <button class="btn btn-primary" type="button" style="background-color: #ffb420; border-color: #ffb420;">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </form>
        <small class="text-muted">We'll never share your email with anyone else.</small>
      </div>
    </div>

    <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

    <!-- Copyright -->
    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> Global Financial Impact. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <a href="#" class="text-white text-decoration-none me-3">Privacy Policy</a>
        <a href="#" class="text-white text-decoration-none me-3">Terms of Service</a>
        <a href="#" class="text-white text-decoration-none">Sitemap</a>
      </div>
    </div>
  </div>
</footer>

<!-- Font Awesome for icons (add this in your head or footer) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
  /* Footer specific styles */
  footer a {
    transition: all 0.3s ease;
  }
  
  footer a:hover {
    color: #ffb420 !important;
    text-decoration: underline !important;
  }
  
  .social-icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(255, 180, 32, 0.2);
    margin-right: 8px;
    transition: all 0.3s ease;
  }
  
  .social-icons a:hover {
    background-color: #ffb420;
    transform: translateY(-3px);
    text-decoration: none !important;
  }
  
  .input-group button:hover {
    background-color: #e6a21d !important;
    border-color: #e6a21d !important;
  }
</style>