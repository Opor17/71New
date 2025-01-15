<?php
session_start();
include 'config/db.php'; // Include your database connection

// Fetch products details from the database based on the IDs in the cart
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM products WHERE id IN ($product_ids)";
    $stmt = $conn->query($query);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Cart Section -->
    <section class="container mx-auto py-12">
        <h2 class="text-3xl font-bold text-center mb-8">ตะกร้า</h2>

        <?php if (empty($_SESSION['cart'])): ?>
            <p class="text-center text-gray-600">Your cart is empty.</p>
        <?php else: ?>
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="w-full bg-gray-200">
                        <th class="py-2 px-4 text-left">สินค้า</th>
                        <th class="py-2 px-4 text-left">ชื่อสินค้า</th>
                        <th class="py-2 px-4 text-left">ไซส์</th>
                        <th class="py-2 px-4 text-left">จํานวน</th>
                        <th class="py-2 px-4 text-left">ราคา</th>
                        <th class="py-2 px-4 text-left">รวม</th>
                        <th class="py-2 px-4 text-left">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0;
                    foreach ($cart_items as $item):
                        // Loop through sizes in the cart for this item
                        foreach ($_SESSION['cart'][$item['id']] as $size => $quantity):
                            $item_total = $item['price'] * $quantity;
                            $total_price += $item_total;
                    ?>
                        <tr>
                            <td class="py-2 px-4">
                                <img src="uploads/<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>" class="w-[100px]">
                            </td>
                            <td class="py-2 px-4">
                                <?php echo $item['product_name']; ?>
                            </td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($size); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($quantity); ?></td>
                            <td class="py-2 px-4">฿<?php echo number_format($item['price'], 2); ?></td>
                            <td class="py-2 px-4">฿<?php echo number_format($item_total, 2); ?></td>
                            <td class="py-2 px-4">
                                <a href="cart.php?remove=<?php echo $item['id']; ?>&size=<?php echo urlencode($size); ?>" class="text-red-600">ลบ</a>
                            </td>
                        </tr>
                    <?php 
                        endforeach; // End of size loop
                        endforeach; // End of item loop
                    ?>
                    <tr>
                        <td colspan="5" class="py-2 px-4 font-bold text-right">รวม:</td>
                        <td class="py-2 px-4 font-bold">฿<?php echo number_format($total_price, 2); ?></td>
                        <td class="py-2 px-4">
                            <a href="checkout.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg">ดำเนินการชำระเงิน</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>

</html>
