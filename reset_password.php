<?php
session_start();
// Database connection setup using PDO
include 'config/db.php';

// Get token from URL
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Process the new password submission
        $newPassword = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validate passwords
        if ($newPassword !== $confirmPassword) {
            echo "<script>Swal.fire('Error!', 'Passwords do not match!', 'error');</script>";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $newPassword)) {
            echo "<script>Swal.fire('Error!', 'Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a number.', 'error');</script>";
        } else {
            // Check if the token exists and is valid
            $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $email = $result['email'];


            if ($result) {
                
                // Update password in the users table
                $updateStmt = $conn->prepare("UPDATE users SET user_password = :newPassword WHERE user_email = :email");
                $updateExecuted = $updateStmt->execute([':newPassword' => $newPassword, ':email' => $email]);
                
                if ($updateExecuted) {

                    // Remove the reset token from the database
                    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
                    $deleteStmt->execute([':email' => $email]);
                    
                    $_SESSION['success'] = 'Password updated successfully!';
                    header("Location: login.php");
                } else {
                    echo "<script>Swal.fire('Error!', 'Failed to update password. Please try again.', 'error');</script>";
                }
            } else {
                
                echo "<script>Swal.fire('Error!', 'Invalid or expired token.', 'error');</script>";
            }
        }
    }
        // Display the password reset form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body class="bg-gray-100">
        <?php include 'components/navbar.php'; ?>
        <div class="p-8">
            <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4">รีเซ็ตรหัสผ่าน</h2>
                <?php if ($token): ?>
                    <form id="resetPasswordForm" method="POST">
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน:</label>
                            <input type="password" name="password" id="password" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่าน:</label>
                            <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        </div>
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            ยืนยัน
                        </button>
                    </form>
                <?php else: ?>
                    <p>Invalid request. No token provided.</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;

            document.getElementById('resetPasswordForm').onsubmit = function(event) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (!passwordPattern.test(password)) {
                    event.preventDefault();
                    Swal.fire('Error!', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร รวมถึงตัวเล็กและตัวใหญ่ และตัวเลข', 'error');
                } else if (password !== confirmPassword) {
                    event.preventDefault();
                    Swal.fire('Error!', 'Passwords do not match!', 'error');
                }
            };
        </script>
        </body>
        </html>
        <?php
    
} catch (Exception $e) {
    echo "<script>Swal.fire('Error!', 'An unexpected error occurred. Please try again later.', 'error');</script>";
}
?>
