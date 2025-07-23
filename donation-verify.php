<?php
// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/.env';

// Check if reference is provided
if (!isset($_GET['reference']) || !isset($_GET['program_id'])) {
    $_SESSION['error_message'] = "Invalid verification request";
    header("Location: programs.php");
    exit();
}

$reference = $_GET['reference'];
$programId = (int)$_GET['program_id'];

// Verify payment with Paystack API
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer sk_test_41008269e1c6f30a68e89226ebe8bf9628c9e3ae", // Replace with your secret key
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    $_SESSION['error_message'] = "Payment verification failed: " . $err;
    header("Location: program.php?id=" . $programId);
    exit();
}

$result = json_decode($response, true);

if (!$result || !$result['status']) {
    $_SESSION['error_message'] = "Payment verification failed";
    header("Location: program.php?id=" . $programId);
    exit();
}

// Payment was successful
$amount = $result['data']['amount'] / 100; // Convert from kobo to naira
$email = $result['data']['customer']['email'];
$firstName = $result['data']['metadata']['custom_fields'][0]['value'] ?? '';
$lastName = $result['data']['metadata']['custom_fields'][1]['value'] ?? '';

// Save donation to database
$conn->begin_transaction();

try {
    // 1. Record the donation
    $query = "INSERT INTO donations (program_id, amount, donor_email, donor_first_name, donor_last_name, payment_reference, status) 
              VALUES (?, ?, ?, ?, ?, ?, 'successful')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idssss", $programId, $amount, $email, $firstName, $lastName, $reference);
    $stmt->execute();
    $donationId = $stmt->insert_id;
    $stmt->close();

    // 2. Update program's raised amount
    $updateQuery = "UPDATE programs SET amount_raised = amount_raised + ?, progress = (amount_raised + ?) / funding_goal * 100 WHERE program_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddi", $amount, $amount, $programId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Send email receipt (you would implement this)
    // sendDonationReceipt($email, $amount, $programId, $reference);

    $_SESSION['success_message'] = "Thank you for your donation of ₦" . number_format($amount, 2) . "!";
    header("Location: program.php?id=" . $programId);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Error processing your donation: " . $e->getMessage();
    header("Location: program.php?id=" . $programId);
    exit();
}
?>