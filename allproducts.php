<?php
session_start();
include 'config/db.php';

// Fetch categories from the database
$categoryQuery = "SELECT DISTINCT category_name FROM products WHERE status != 'delete'";
$categoryStmt = $conn->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products with optional search and category filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT * FROM products WHERE product_name LIKE :search AND status != 'delete'";
$params = [':search' => '%' . $search . '%'];

if ($category) {
    $query .= " AND category_name = :category";
    $params[':category'] = $category;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a product to the cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'] ?? 1; // Default quantity to 1 if not set
    $size = $_POST['size'];

    // Fetch product stock based on size
    $sizeColumn = $size === "L" ? "stock_L" : ($size === "XL" ? "stock_XL" : null);
    if ($sizeColumn) {
        $query = "SELECT $sizeColumn FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);
        $stock = $stmt->fetchColumn();

        // Check if there is enough stock
        if ($stock && $stock >= $quantity) {
            // Check if the product with the selected size is already in the cart
            if (isset($_SESSION['cart'][$product_id][$size])) {
                // Update the quantity
                $new_quantity = $_SESSION['cart'][$product_id][$size] + $quantity;

                // Verify that the new quantity does not exceed stock
                if ($new_quantity <= $stock) {
                    $_SESSION['cart'][$product_id][$size] = $new_quantity;
                } else {
                    echo "<script>alert('Not enough stock to add more of this product.');</script>";
                }
            } else {
                // If product isn't in the cart, add it with the initial quantity
                $_SESSION['cart'][$product_id][$size] = $quantity;
            }

            // Update the total cart count
            $_SESSION['cart_count'] = array_sum(array_map('array_sum', $_SESSION['cart']));
        } else {
            echo "<script>alert('Not enough stock for this product.');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Search and Category Filters -->
    <section class="container mx-auto py-6">
        <form method="GET" action="" class="flex items-center justify-between space-x-4 mb-4">
            <!-- Search Bar -->
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">

            <!-- Category Filter -->
            <select name="category" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_name']; ?>" <?php echo $cat['category_name'] === $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Submit Button -->
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                Search
            </button>
        </form>
    </section>

    <div class="flex">
        <div class="w-[300px]">
            <?php include 'components/main_sidebar_allproducts.php'; ?>
        </div>

        <div class="w-full">
            <!-- Product Showcase Section -->
            <section class="container mx-auto py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="bg-gray-100 p-6 rounded-lg shadow-md transition-transform transform hover:scale-105 duration-300">
                                <img src="uploads/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>" class="h-40 w-full object-cover mb-4 rounded-lg shadow-md">
                                <h3 class="text-2xl font-bold mb-2"><?php echo $product['product_name']; ?></h3>
                                <p class="text-gray-700 mb-2 text-lg">฿<?php echo number_format($product['price'], 2); ?></p>

                                <!-- Add to Cart Form -->
                                <form action="" method="POST" class="flex items-center">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                                    <!-- Size selection dropdown -->
                                    <select name="size" required class="border border-gray-300 rounded-lg mr-2 p-1">
                                        <option value="">Select Size</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                    </select>

                                    <input type="number" name="quantity" min="1" value="1" class="border border-gray-300 rounded-lg w-16 mr-2" hidden>

                                    <button type="submit" name="add_to_cart" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Add to Cart</button>
                                </form>

                                <a href="product.php?id=<?php echo $product['id']; ?>" class="block mt-2 text-center bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition duration-300">View Product</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-500">No products found.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>
</html>
