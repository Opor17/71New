<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';
include 'config/db.php';

// Configure email variables
if (!isset($_POST['email'])){
    header("Location: index.php");
    exit;
}

$userEmail = $_POST['email'];
$resetToken = bin2hex(random_bytes(16)); // Generate a secure random token
$resetLink = "https://71bootlegstore.com/reset_password.php?token=" . $resetToken; // Link with token

// Calculate expiration time + 1 day
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

// Insert reset token into the database
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
$stmt->execute([
    ':email' => $userEmail,
    ':token' => $resetToken,
    ':expires_at' => $expiresAt
]);

// Initialize PHPMailer
$mail = new PHPMailer(true);
$emailSent = false;

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';        // Gmail SMTP server
    $mail->SMTPAuth = true;                 // Enable SMTP authentication
    $mail->Username = 'ppuresitthiporn@gmail.com';   // Your Gmail address
    $mail->Password = 'uqff uokh eznw jkvp';    // Your Gmail app password or generated token
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587;                          // TCP port for TLS

    // Recipients
    $mail->setFrom('ppuresitthiporn@gmail.com', 'Bootleg');
    $mail->addAddress($userEmail); // Add recipient's email address

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "
        <p>Hello,</p>
        <p>We received a request to reset your password. Please click the link below to reset your password:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>If you didn't request this, please ignore this email.</p>
        <p>Thank you!</p>
    ";
    $mail->AltBody = "Hello,\n\nWe received a request to reset your password. Please click the link below to reset your password:\n\n$resetLink\n\nIf you didn't request this, please ignore this email.\n\nThank you!";

    // Send email
    $mail->send();
    $emailSent = true;
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Forgot Password | Bootleg Store</title>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="w-full max-w-md mx-auto mt-8 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-semibold text-gray-800 text-center">Password Reset Requested</h1>
        <p class="mt-4 text-gray-600 text-center">
            If an account with this email exists, a password reset link has been sent to it.
        </p>
        <div class="flex justify-center mt-6">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium underline">
                Back to Home
            </a>
        </div>
    </div>

    <script>
        <?php if ($emailSent): ?>
        // Show a success alert if the email was sent
        Swal.fire({
            title: 'Email Sent!',
            text: 'A password reset link has been sent to your email address.',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4f46e5'
        });
        <?php endif; ?>
    </script>
</body>
</html>
