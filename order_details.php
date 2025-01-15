<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<h1>Order not found!</h1>";
        exit;
    }

    $order_items_query = "SELECT oi.*, p.product_name, p.image_path FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?";
    $order_items_stmt = $conn->prepare($order_items_query);
    $order_items_stmt->execute([$order_id]);
    $order_items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<h1>No order specified!</h1>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Order Details Section -->
    <section class="container mx-auto py-12">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div>
                <?php include 'components/menu_profile.php'; ?>
            </div>
            <div class="col-span-3">
                <div class="border border-gray-300 p-4 bg-white shadow-md rounded-lg">
                    <h2 class="text-2xl font-bold mb-4">Order Details</h2>
                    <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
                    <p><strong>Date:</strong> <?php echo date("Y-m-d H:i:s", strtotime($order['order_date'])); ?></p>
                    <p><strong>Total Price:</strong> ฿<?php echo number_format($order['total_price'], 2); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                    <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['track_shipping']); ?></p>
                    <p><strong>Shipper</strong> <?php echo htmlspecialchars($order['track_shipper']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <?php if ($order['payment_slip']): ?>
                        <p><strong>Payment Slip:</strong> <a href="uploads/<?php echo htmlspecialchars($order['payment_slip']); ?>" target="_blank">View Slip</a></p>
                    <?php endif; ?>

                    <!-- Order Items Table -->
                    <h4 class="mt-8 mb-4 text-xl font-bold">Items in Order</h4>
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Image</th>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Product Name</th>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Quantity</th>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Size</th>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Unit Price</th>
                                <th class="py-3 px-6 bg-gray-200 text-gray-600 font-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr class="border-b">
                                    <td class="py-4 px-6">
                                        <img src="uploads/<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="w-16 h-16 object-cover rounded-md">
                                    </td>
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="py-4 px-6 text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="py-4 px-6 text-center"><?php echo $item['size']; ?></td>
                                    <td class="py-4 px-6">฿<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="py-4 px-6">฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>
</html>
