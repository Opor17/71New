<?php
session_start();

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
<body class="bg-white">
    <?php include 'components/navbar.php'; ?>

    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Forgot Password</h2>
            <form method="POST" action="send_email_forgotpasswrod.php">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <button type="submit" name="reset_password"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none">
                        Reset Password
                    </button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Sign Up</a>
            </p>
        </div>
    </div>

</body>
</html>
