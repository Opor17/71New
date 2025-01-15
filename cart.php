<?php
session_start();
include 'config/db.php'; // Include database connection if needed

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0;
}

// Check stock before adding a product to the cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $size = $_POST['size'];

    if ($size == "L"){
    // Fetch product stock from the database
    $query = "SELECT stock_L FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();

    if ($stock && $stock >= $quantity) {
        // Proceed to add or update the quantity in the cart
        if (isset($_SESSION['cart'][$product_id][$size])) {
            $_SESSION['cart'][$product_id][$size] += $quantity;
        } else {
            $_SESSION['cart'][$product_id][$size] = $quantity;
        }
        $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
    } else {
        echo "<script>alert('Not enough stock for this product.');</script>";
    }}
    else if ($size == "XL"){
            // Fetch product stock from the database
    $query = "SELECT stock_XL FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();

    if ($stock && $stock >= $quantity) {
        // Proceed to add or update the quantity in the cart
        if (isset($_SESSION['cart'][$product_id][$size])) {
            $_SESSION['cart'][$product_id][$size] += $quantity;
        } else {
            $_SESSION['cart'][$product_id][$size] = $quantity;
        }
        $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
    } else {
        echo "<script>alert('Not enough stock for this product.');</script>";
    }}
}

// Check stock before increasing quantity
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $action = $_POST['action'];



    if ($size == "L"){

    $query = "SELECT stock_L FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();

    if ($action == 'increase' && $_SESSION['cart'][$product_id][$size] < $stock) {
        $_SESSION['cart'][$product_id][$size]++;
    } elseif ($action == 'decrease' && $_SESSION['cart'][$product_id][$size] > 1) {
        $_SESSION['cart'][$product_id][$size]--;
    } else {
        echo "<script>alert('Cannot exceed available stock or reduce below 1.');</script>";
    }


    $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
    }
    else if ($size == "XL"){
        $query = "SELECT stock_XL FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);
        $stock = $stmt->fetchColumn();
    
        if ($action == 'increase' && $_SESSION['cart'][$product_id][$size] < $stock) {
            $_SESSION['cart'][$product_id][$size]++;
        } elseif ($action == 'decrease' && $_SESSION['cart'][$product_id][$size] > 1) {
            $_SESSION['cart'][$product_id][$size]--;
        } else {
            echo "<script>alert('Cannot exceed available stock or reduce below 1.');</script>";
        }
    
        $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
    }
    
}


// Handle removing a product from the cart
if (isset($_GET['remove']) && isset($_GET['size'])) {
    $product_id = $_GET['remove'];
    $size = $_GET['size'];

    if (isset($_SESSION['cart'][$product_id][$size])) {
        unset($_SESSION['cart'][$product_id][$size]); // Remove the specified size
    }

    // If there are no more sizes for this product, remove the product from the cart
    if (empty($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    // Update cart count
    $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
}


// Handle clearing the entire cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0;
}

// Fetch products for display in the cart
$products = [];
if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM products WHERE id IN ($product_ids)";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate the total price
function calculateTotalPrice($products, $cart)
{
    $total = 0;
    foreach ($products as $product) {
        $product_id = $product['id'];
        foreach ($cart[$product_id] as $size => $quantity) {
            $total += $product['price'] * $quantity;
        }
    }
    return $total;
}

$total_price = calculateTotalPrice($products, $_SESSION['cart']);

// Get the total number of items in the cart
function getCartTotal()
{
    return array_sum(array_map('array_sum', $_SESSION['cart']));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Cart Section -->
    <div class="container mx-auto py-12">
        <h2 class="text-3xl font-bold text-center mb-8">ตะกร้า</h2>

        <!-- Check if cart is empty -->
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center">
                <p class="text-lg">Your cart is empty!</p>
                <a href="index.php" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Go Shopping</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Cart Items -->
                <div>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white p-6 rounded-lg shadow-md mb-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="uploads/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>" class="h-20 w-20 object-cover rounded-lg mr-4">
                                <div>
                                    <h3 class="text-lg font-bold"><?php echo $product['product_name']; ?></h3>
                                    <p class="text-gray-700">฿<?php echo number_format($product['price'], 2); ?></p>

                                    <!-- Size selection and quantity controls -->
                                    <?php if (isset($_SESSION['cart'][$product['id']])): ?>
                                        <?php foreach ($_SESSION['cart'][$product['id']] as $size => $quantity): ?>
                                            <div class="flex mt-2 items-center">
                                                <p class="text-gray-700">Size: <?php echo htmlspecialchars($size); ?> x <?php echo $quantity; ?></p>
                                                <form action="cart.php" method="POST" class="ml-2">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="size" value="<?php echo htmlspecialchars($size); ?>">
                                                    <input type="hidden" name="action" value="decrease">
                                                    <button type="submit" name="update_quantity" class="bg-gray-300 text-black px-2 py-1 rounded-lg">-</button>
                                                </form>
                                                <form action="cart.php" method="POST" class="ml-2">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="size" value="<?php echo htmlspecialchars($size); ?>">
                                                    <input type="hidden" name="action" value="increase">
                                                    <button type="submit" name="update_quantity" class="bg-gray-300 text-black px-2 py-1 rounded-lg">+</button>
                                                </form>
                                                <a href="cart.php?remove=<?php echo $product['id']; ?>&size=<?php echo urlencode($size); ?>" class="text-red-600 hover:underline ml-4">ลบ</a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



                <!-- Cart Summary -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">รวมมูลค่าสินค้า</h3>
                    <p class="text-gray-700">Total items: <?php echo getCartTotal(); ?></p>
                    <p class="text-gray-700 font-bold">Total price: ฿<?php echo number_format($total_price, 2); ?></p>

                    <div class="mt-4 flex flex-col space-y-2">
                        <!-- Redirect to the checkout page -->
                        <a href="cart_page.php" class="block text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">ดำเนินการชำระเงิน</a>
                        <a href="cart.php?clear=true" class="block text-center bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">ลบตะกร้า</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>

</html>