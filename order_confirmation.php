<?php
session_start();
include 'config/db.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the latest order for the user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<h1>No orders found!</h1>";
    exit;
}

// Fetch order items
$order_id = $order['id'];
$order_items_query = "SELECT oi.*, p.product_name FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?";
$order_items_stmt = $conn->prepare($order_items_query);
$order_items_stmt->execute([$order_id]);
$order_items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Confirmation Section -->
    <div class="container mx-auto py-12">
        <h2 class="text-3xl font-bold text-center mb-8">Order Confirmation</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-4">Thank you for your order!</h3>
            <p>Your order has been placed successfully. Here are the details:</p>

            <h4 class="mt-4 font-bold">Order Details</h4>
            <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
            <p><strong>Name:</strong> <?php echo $order['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
            <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
            <p><strong>Total Price:</strong> ฿<?php echo number_format($order['total_price'], 2); ?></p>
            <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['track_shipping']); ?></p>
            <p><strong>Shipper</strong> <?php echo htmlspecialchars($order['track_shipper']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <?php if ($order['payment_slip']): ?>
                <p><strong>Payment Slip:</strong> <a href="uploads/<?php echo htmlspecialchars($order['payment_slip']); ?>" target="_blank">View Slip</a></p>
            <?php endif; ?>
            <p><strong>Order Date:</strong> <?php echo date("Y-m-d H:i:s", strtotime($order['order_date'])); ?></p>

            <h4 class="mt-4 font-bold">Items in Your Order:</h4>
            <ul>
                <?php foreach ($order_items as $item): ?>
                    <li><?php echo htmlspecialchars($item['product_name']); ?> x <?php echo $item['quantity']; ?> size <?php echo $item['size']; ?> - ฿<?php echo number_format($item['price'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>
</html>
