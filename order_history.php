<?php
try {
    session_start();
    include 'config/db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Fetch user details
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h1>User not found!</h1>";
        exit;
    }

    // Pagination setup
    $records_per_page = 10; // Define how many records per page
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $records_per_page;

    // Fetch orders with limit and offset for pagination
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of orders to calculate the total number of pages
    $total_orders_query = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($total_orders_query);
    $stmt->execute([$user_id]);
    $total_orders = $stmt->fetchColumn();
    $total_pages = ceil($total_orders / $records_per_page);

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
            <div class="col-span-3">
                <div class="border border-gray-300 p-4 ">
                    <h2 class="text-2xl font-bold mb-4">รายการสินค้าที่ซื้อ</h2>
                    <div>
                        <?php if (empty($orders)): ?>
                            <p class="text-center">You have not placed any orders yet.</p>
                        <?php else: ?>
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border">ID คำสั่งซื้อ</th>
                                        <th class="py-2 px-4 border">วันสั่งซื้อ</th>
                                        <th class="py-2 px-4 border">ราคารวม</th>
                                        <th class="py-2 px-4 border">สถานะ</th>
                                        <th class="py-2 px-4 border">รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="py-2 px-4 border"><?php echo $order['id']; ?></td>
                                            <td class="py-2 px-4 border"><?php echo date("d-m-Y H:i:s", strtotime($order['order_date'])); ?></td>
                                            <td class="py-2 px-4 border">฿<?php echo number_format($order['total_price'], 2); ?></td>
                                            <td class="py-2 px-4 border"><?php echo htmlspecialchars($order['status']); ?></td>
                                            <td class="py-2 px-4 border">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:underline">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Pagination Controls -->
                            <div class="mt-4 flex justify-center">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?>" class="px-3 py-1 border rounded-l-md bg-blue-500 text-white">Previous</a>
                                <?php endif; ?>

                                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                    <a href="?page=<?php echo $page; ?>" class="px-3 py-1 border <?php echo ($page == $current_page) ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'; ?>"><?php echo $page; ?></a>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>" class="px-3 py-1 border rounded-r-md bg-blue-500 text-white">Next</a>
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
