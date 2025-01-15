<?php
try {
    session_start();
    include 'config/db.php'; // Include the database connection

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Fetch user information
    $sql = "SELECT * FROM users WHERE user_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h1>User not found!</h1>";
        exit;
    }

    // Pagination settings
    $records_per_page = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $records_per_page;

    // Fetch total count of login history
    $total_sql = "SELECT COUNT(*) FROM user_login WHERE user_id = :id";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->bindParam(':id', $_SESSION['user_id']);
    $total_stmt->execute();
    $total_records = $total_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Fetch paginated login history
    $login_history_sql = "SELECT created_at FROM user_login WHERE user_id = :id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $login_stmt = $conn->prepare($login_history_sql);
    $login_stmt->bindParam(':id', $_SESSION['user_id']);
    $login_stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $login_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $login_stmt->execute();
    $login_history = $login_stmt->fetchAll(PDO::FETCH_ASSOC);

    $error = ''; // Define error message variable

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login List | Bootleg Store</title>
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
            <div class="col-span-3">
                <div class="border border-gray-300 p-4">
                    <h2 class="text-2xl font-bold mb-4">รายการเข้าสู่ระบบ</h2>

                    <!-- Display error message if any -->
                    <?php if ($error): ?>
                        <p class="text-red-500"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <!-- Display Login History Table -->
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-2 px-4 border-b text-left">วันที่</th>
                                <th class="py-2 px-4 border-b text-left">เวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($login_history as $entry): ?>
                                <?php
                                    // Format date and time
                                    $datetime = new DateTime($entry['created_at']);
                                    $date = $datetime->format('d-m-Y');
                                    $time = $datetime->format('H:i:s');
                                ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo $date; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $time; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($login_history)): ?>
                                <tr>
                                    <td colspan="2" class="py-2 px-4 text-center text-gray-500">No login history available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <?php if ($total_pages > 1): ?>
                            <div class="flex justify-center space-x-2">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?>" class="px-3 py-1 bg-gray-300 text-gray-700 rounded">Previous</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" class="px-3 py-1 <?php echo $i === $current_page ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-700'; ?> rounded">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>" class="px-3 py-1 bg-gray-300 text-gray-700 rounded">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
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
