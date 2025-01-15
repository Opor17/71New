<?php
try {
    session_start();
    include 'config/db.php'; // Include the database connection

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $sql = "SELECT * FROM users WHERE user_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h1>User not found!</h1>";
        exit;
    }

    $error = '';
    $success = false; // Flag to track success
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['password'];
        $new_password = $_POST['new_password'];

        // Validate fields
        if (empty($current_password) || empty($new_password)) {
            $error = "Please fill in all fields.";
        } elseif ($current_password !== $user['user_password']) {
            $error = "Current password is incorrect.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
            $error = "New password must contain at least 8 characters, including uppercase, lowercase, and a number.";
        } else {
            // Hash the new password and update in the database
            $hashed_new_password = $new_password; // Consider using password hashing in production

            $update_sql = "UPDATE users SET user_password = :new_password WHERE user_id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':new_password', $hashed_new_password);
            $update_stmt->bindParam(':id', $_SESSION['user_id']);
            $update_stmt->execute();

            $success = true; // Set success flag
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Password | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Product Showcase Section -->
    <section class="container mx-auto py-12">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div>
                <?php include 'components/menu_profile.php'; ?>
            </div>
            <div class="grid-cols-subgrid gap-4 col-span-3">
                <div class="border border-gray-300 p-4">
                    <h2 class="text-2xl font-bold mb-4">เปลี่ยนรหัสผ่านของฉัน</h2>
                    
                    <form method="post">
                        <div>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div class="flex">
                                    <div style="width: 350px;" class="text-left items-center flex text-lg">
                                        รหัสผ่านปัจจุบัน
                                    </div>
                                    <div>
                                        <input type="password" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="password" required>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div class="flex">
                                    <div style="width: 350px;" class="text-left items-center flex text-lg">
                                        รหัสผ่านใหม่
                                    </div>
                                    <div>
                                        <input type="password" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="new_password" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button class="inline-block px-4 py-2 leading-none border rounded text-white bg-[#1E1E1E] mt-10 lg:mt-0 hover:bg-gray-800 transition duration-200">
                                เปลี่ยนรหัสผ่านของฉัน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- SweetAlert for Success and Error Messages -->
    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Password updated successfully!',
            });
        </script>
    <?php elseif ($error): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>',
            });
        </script>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>
</html>

<?php
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
