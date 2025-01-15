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



?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile | Bootleg Store</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <div class="border border-gray-300 p-4 ">
                        <h2 class="text-2xl font-bold mb-4">บัญชี</h2>
                        <div>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div>
                                    <h3 class="text-lg font-bold">อีเมล</h3>
                                    <p class="text-gray-600"><?php echo $user['user_email']; ?></p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">โทรศัพท์</h3>
                                    <p class="text-gray-600"><?php echo $user['user_phone']; ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div>
                                    <h3 class="text-lg font-bold">ชื่อ</h3>
                                    <p class="text-gray-600"><?php echo $user['user_firstname']; ?> <?php echo $user['user_lastname']; ?></p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">วันเกิด</h3>
                                    <p class="text-gray-600"><?php echo date("d-m-Y", strtotime($user['user_birthday'])); ?>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div>
                                    <h3 class="text-lg font-bold">ที่อยู่</h3>
                                    <p class="text-gray-600"><?php echo $user['user_address']; ?></p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">เพศ</h3>
                                    <p class="text-gray-600"><?php echo $user['user_gender']; ?></p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>


        </section>

        <!-- Footer -->
        <?php include 'components/footer.php'; ?>
    </body>

    </html>

<?php
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>