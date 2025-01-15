<?php
session_start();
include 'config/db.php'; // Include the database connection

// Fetch products from the database
$query = "SELECT * FROM products WHERE NOT status = 'delete' ORDER BY id DESC LIMIT 8"; // Adjust table name as necessary
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch carousel items from the database
$carouselQuery = "SELECT * FROM carousel"; // Adjust table name as necessary
$carouselStmt = $conn->prepare($carouselQuery);
$carouselStmt->execute();
$carousel_items = $carouselStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch promotion items from the database
$promotionQuery = "SELECT * FROM promotion"; // Adjust table name as necessary
$promotionStmt = $conn->prepare($promotionQuery);
$promotionStmt->execute();
$promotion_items = $promotionStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0; // Initialize cart count if it doesn't exist
}

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

    <!-- Promotion Modal -->
    <div id="promotionModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
        <div class="bg-white p-8 rounded-lg max-w-lg w-full relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>

            <!-- Slideshow container -->
            <div id="promotionCarousel" class="relative">
                <?php foreach ($promotion_items as $index => $item): ?>
                    <div class="promotion-slide <?php echo $index === 0 ? 'active' : 'hidden'; ?>">
                        <img src="uploads/<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-auto rounded-lg">
                        <h3 class="text-2xl font-bold mt-4"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="mt-2 text-gray-700"><?php echo htmlspecialchars($item['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Slide navigation controls -->
            <button onclick="prevSlide()" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-gray-200 text-gray-800 px-2 py-1 rounded-full hover:bg-gray-300">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="nextSlide()" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-gray-200 text-gray-800 px-2 py-1 rounded-full hover:bg-gray-300">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- JavaScript for Modal, Slide Control, and Auto-Slide -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.promotion-slide');
        let slideInterval;

        // Show the modal on page load
        window.onload = function() {
            document.getElementById('promotionModal').classList.remove('hidden');
            startAutoSlide(); // Start auto-sliding when the modal opens
        };

        // Function to close the modal and stop auto-slide
        function closeModal() {
            document.getElementById('promotionModal').classList.add('hidden');
            stopAutoSlide(); // Stop auto-sliding when the modal is closed
        }

        // Show a specific slide based on index
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('hidden', i !== index);
            });
            currentSlide = index;
        }

        // Go to the next slide
        function nextSlide() {
            showSlide((currentSlide + 1) % slides.length);
        }

        // Go to the previous slide
        function prevSlide() {
            showSlide((currentSlide - 1 + slides.length) % slides.length);
        }

        // Start auto-sliding
        function startAutoSlide() {
            slideInterval = setInterval(nextSlide, 3000); // Change slide every 3 seconds
        }

        // Stop auto-sliding
        function stopAutoSlide() {
            clearInterval(slideInterval);
        }
    </script>


    <div class="flex">
        <div class="w-[300px]">
            <?php include 'components/main_sidebar.php'; ?>
        </div>


        <div class="w-full">


            <!-- Hero Section -->
            <div class="mx-auto">
                <div id="default-carousel" class="relative" data-carousel="static">
                    <div class="overflow-hidden relative h-64 rounded-lg">
                        <!-- Carousel items -->
                        <?php foreach ($carousel_items as $index => $item): ?>
                            <div class="<?php echo $index === 0 ? '' : 'hidden'; ?> duration-700 ease-in-out" data-carousel-item>
                                <img src="uploads/<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Slider controls -->
                    <button type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-gray-800 group-hover:bg-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </button>
                    <button type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-gray-800 group-hover:bg-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </button>
                </div>
                <script src="https://unpkg.com/flowbite@1.4.0/dist/flowbite.js"></script>
            </div>

            <!-- Product Showcase Section -->
            <section class="container mx-auto py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
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
                </div>
            </section>
        </div>

    </div>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>

</html>