<?php
// Start session
session_start();

// Include database configuration and Dotenv
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php'; // Assuming you're using Composer

use Dotenv\Dotenv;
// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();

// Check if program_id is provided
if (!isset($_POST['program_id']) || !isset($_POST['amount'])) {
    $_SESSION['error_message'] = "Invalid donation request";
    header("Location: programs.php");
    exit();
}

// Get program details
$programId = (int)$_POST['program_id'];
$amount = (float)$_POST['amount'];

// Validate amount
if ($amount < 100) {
    $_SESSION['error_message'] = "Minimum donation amount is ₦100";
    header("Location: program.php?id=" . $programId);
    exit();
}

// Get program details
$query = "SELECT * FROM programs WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $programId);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

if (!$program) {
    $_SESSION['error_message'] = "Program not found";
    header("Location: programs.php");
    exit();
}

// Include header
$pageTitle = "Donate to " . htmlspecialchars($program['title']);
include 'include/header.php';
?>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow">
          <div class="card-body p-5">
            <h2 class="card-title text-center mb-4">Donate to <?php echo htmlspecialchars($program['title']); ?></h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
              <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <div class="text-center mb-4">
              <img src="<?php echo htmlspecialchars($program['featured_image'] ?? 'default-program.jpg'); ?>" 
                   class="img-fluid rounded" style="max-height: 200px;" alt="<?php echo htmlspecialchars($program['title']); ?>">
            </div>
            
            <div class="mb-4">
              <p class="lead text-center">Your donation amount: <strong>₦<?php echo number_format($amount, 2); ?></strong></p>
              <p class="text-muted text-center"><?php echo htmlspecialchars($program['excerpt'] ?? ''); ?></p>
            </div>
            
            <form id="paymentForm">
              <input type="hidden" name="program_id" value="<?php echo $programId; ?>">
              <input type="hidden" name="amount" value="<?php echo $amount * 100; ?>"> <!-- Paystack uses kobo -->
              
              <div class="mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" required value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
              </div>
              
              <div class="mb-3">
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" class="form-control" id="first_name" required value="<?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : ''; ?>">
              </div>
              
              <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" value="<?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?>">
              </div>
              
              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone">
              </div>
              
              <div class="mb-3">
                <label for="message" class="form-label">Message (Optional)</label>
                <textarea class="form-control" id="message" rows="3"></textarea>
              </div>
              
              <div class="d-grid">
                <button type="button" class="btn btn-success btn-lg" id="payButton">Proceed to Payment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Paystack Integration Script -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
  document.getElementById('payButton').addEventListener('click', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    const phone = document.getElementById('phone').value;
    const message = document.getElementById('message').value;
    const amount = document.getElementsByName('amount')[0].value;
    const programId = document.getElementsByName('program_id')[0].value;
    
    // Validate required fields
    if (!email || !firstName) {
      alert('Please fill in all required fields');
      return;
    }
    
    const handler = PaystackPop.setup({
      key: '<?php echo $_ENV['PAYSTACK_PUBLIC']; ?>',
      email: email,
      amount: amount,
      currency: 'NGN',
      ref: 'HBD_' + programId + '_' + Math.floor(Math.random() * 1000000000 + 1),
      metadata: {
        custom_fields: [
          {
            display_name: "First Name",
            variable_name: "first_name",
            value: firstName
          },
          {
            display_name: "Last Name",
            variable_name: "last_name",
            value: lastName || ''
          },
          {
            display_name: "Phone",
            variable_name: "phone",
            value: phone || ''
          },
          {
            display_name: "Message",
            variable_name: "message",
            value: message || ''
          },
          {
            display_name: "Program ID",
            variable_name: "program_id",
            value: programId
          }
        ]
      },
      callback: function(response) {
        // On successful payment
        window.location.href = `donation-verify.php?reference=${response.reference}&program_id=${programId}`;
      },
      onClose: function() {
        // When user closes payment modal
        alert('Payment window closed');
      }
    });
    
    handler.openIframe();
  });
</script>

<?php
include 'include/footer.php';
?>