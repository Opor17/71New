<?php
session_start();
include 'config/db.php'; // Include your database connection

// Check if the product ID is set in the URL
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch the product details from the database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch additional images from the product_images table
$sql_images = "SELECT * FROM product_images WHERE product_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->execute([$product_id]);
$additional_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a product to the cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity']; // Ensure quantity is an integer
    $size  = $_POST['size'];

    // Initialize the cart if it does not exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product with the specific size is already in the cart
    if (isset($_SESSION['cart'][$product_id][$size])) {
        $_SESSION['cart'][$product_id][$size] += $quantity; // Increase quantity
    } else {
        $_SESSION['cart'][$product_id][$size] = $quantity; // Add new product with size
    }

    // Update cart count by summing up quantities of all items in the cart
    $_SESSION['cart_count'] = array_reduce($_SESSION['cart'], fn($count, $product) => $count + array_sum($product), 0);

    header("Location: product.php?id=$product_id");
}

// If product not found, redirect to products page
if (!$product) {
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> | Product Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'components/navbar.php'; ?>

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-6"><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <div class="flex">
            <!-- Main Product Image -->
            <div class="flex-none w-1/2 pr-4">
                <img src="uploads/<?php echo htmlspecialchars($product['image_path']); ?>" alt="Main Product Image" class="w-full h-auto rounded-lg shadow-md cursor-pointer" onclick="openImageModal(this.src)">
                <h3 class="mt-4 text-lg font-semibold">Additional Images</h3>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <?php foreach ($additional_images as $image): ?>
                        <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Product Image" class="h-20 w-full object-cover rounded shadow cursor-pointer" onclick="openImageModal(this.src)">
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="flex-1 pl-4">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h1 class="text-6xl font-bold text-gray-800"><?php echo $product['product_name']; ?></h1>
                    <h3 class="text-2xl font-bold mt-2 text-gray-800">THB <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></h3>

                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <!-- Size Chart Button -->
                    <button onclick="openSizeChart()" class="mt-12 flex items-center">
                        <img src="public/image/size.png" alt="Size icon" class="w-[50px]">
                        <span class="underline ml-2">ตารางขนาด</span>
                    </button>

                    <!-- Add to Cart Form -->
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <!-- Size selection dropdown -->
                        <select name="size" required class="border border-gray-300 rounded-lg p-2">
                            <option value="">Select Size</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                        </select>

                        <!-- Quantity input -->
                        <input type="number" name="quantity" min="1" value="1" required class="border border-gray-300 rounded-lg p-2 ml-2" />

                        <button type="submit" name="add_to_cart" class="bg-[#1E1E1E] text-white px-4 py-2 rounded-lg ml-2">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Image Modal -->
    <div id="imageModal" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <img id="modalImage" src="" alt="Zoomed Image" class="max-w-[90%] max-h-[90%] rounded-lg">
        <span class="close absolute top-5 right-5 text-white text-3xl cursor-pointer" onclick="closeImageModal()">&times;</span>
    </div>

    <!-- Size Chart Modal -->
    <div id="sizeChartModal" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-4 rounded-lg max-w-lg w-full relative">
            <button onclick="closeSizeChart()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">&times;</button>
            <img src="public/image/Size Chart.png" alt="Size Chart" class="w-full h-auto">
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        // Image Modal Functions
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'flex';
            modalImage.src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Size Chart Modal Functions
        function openSizeChart() {
            document.getElementById('sizeChartModal').style.display = 'flex';
        }

        function closeSizeChart() {
            document.getElementById('sizeChartModal').style.display = 'none';
        }

        // Close Modals on Outside Click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeImageModal();
                closeSizeChart();
            }
        };
    </script>
</body>
</html>
