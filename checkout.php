<?php
session_start();
include 'config/db.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sql_shipper = "SELECT * FROM shipper";
$stmt_shipper = $conn->prepare($sql_shipper);
$stmt_shipper->execute();
$shippers = $stmt_shipper->fetchAll(PDO::FETCH_ASSOC);



// Fetch products in the cart
$products = [];
$all_quantities = 0;
if (!empty($_SESSION['cart'])) {
    // Prepare the product IDs for the SQL query
    $product_ids = array_keys($_SESSION['cart']);

    if (count($product_ids) > 0) {
        // Create a placeholder string for the prepared statement
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $query = "SELECT * FROM products WHERE id IN ($placeholders)";

        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<script>alert('Error fetching products: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    }
}

// Calculate the total price
function calculateTotalPrice($products, $cart)
{
    $total = 0;
    foreach ($products as $product) {
        foreach ($cart[$product['id']] as $quantity) {
            $total += $product['price'] * $quantity;
        }
    }
    return $total;
}

$total_price = calculateTotalPrice($products, $_SESSION['cart']);

// Handle checkout submission
if (isset($_POST['submit_order'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];
    $track_shipper = $_POST['shipper'];
    $address .= " เบอร์โทรศัพท์: " . $_POST['phone_num'];

    $sql_shipper = "SELECT * FROM shipper WHERE id = ?";
    $stmt_shipper = $conn->prepare($sql_shipper);
    $stmt_shipper->execute([$track_shipper]);
    $shipper = $stmt_shipper->fetch(PDO::FETCH_ASSOC);


    if (empty($name) || empty($email) || empty($address) || empty($payment_method)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        $total_price = calculateTotalPrice($products, $_SESSION['cart']);


        if ($all_quantities >= 10) {
            $total_price = ($shipper['price'] * 2) + $total_price;
        } else {
            $total_price = $shipper['price'] + $total_price;
        }

        // Handle file upload
        if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $file_name = basename($_FILES['payment_slip']['name']);
            $target_file = $upload_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($_FILES['payment_slip']['tmp_name'], $target_file)) {
                    try {
                        $order_query = "INSERT INTO orders (user_id, name, email, address, total_price, payment_method, payment_slip, track_shipper) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($order_query);
                        $stmt->execute([$user_id, $name, $email, $address, $total_price, $payment_method, $file_name, $shipper['name']]);

                        $order_id = $conn->lastInsertId();

                        foreach ($products as $product) {
                            foreach ($_SESSION['cart'][$product['id']] as $size => $quantity) {
                                $item_price = $product['price'];
                                $order_item_query = "INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)";
                                $stmt = $conn->prepare($order_item_query);
                                $stmt->execute([$order_id, $product['id'], $size, $quantity, $item_price]);

                                if ($size == 'L') {
                                    $update_stock_query = "UPDATE products SET stock_L = stock_L - ? WHERE id = ?";
                                    $update_stmt = $conn->prepare($update_stock_query);
                                    $update_stmt->execute([$quantity, $product['id']]);
                                } else {
                                    $update_stock_query = "UPDATE products SET stock_XL = stock_XL - ? WHERE id = ?";
                                    $update_stmt = $conn->prepare($update_stock_query);
                                    $update_stmt->execute([$quantity, $product['id']]);
                                }
                            }
                        }

                        $_SESSION['cart'] = [];
                        $_SESSION['cart_count'] = 0;

                        header("Location: order_confirmation.php");
                        exit;
                    } catch (PDOException $e) {
                        echo "<script>alert('Error processing your order: " . htmlspecialchars($e->getMessage()) . "');</script>";
                    }
                } else {
                    echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                }
            } else {
                echo "<script>alert('Invalid file type. Only JPG, PNG, GIF, and PDF files are allowed.');</script>";
            }
        } else {
            echo "<script>alert('No file uploaded or upload error.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'components/navbar.php'; ?>

    <div class="container mx-auto py-12">
        <h2 class="text-3xl font-bold text-center mb-8">สรุปคำสั่งซื้อ</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold mb-4">ตะกร้า</h3>
                <?php if (!empty($products)): ?>
                    <ul>
                        <?php foreach ($products as $product): ?>
                            <li class="mb-4">
                                <div class="text-left">
                                    <img src="uploads/<?php echo $product['image_path']; ?>" alt="" srcset="" class="w-[100px]">
                                    <span class="font-bold text-xl ml-4"><?php echo htmlspecialchars($product['product_name']); ?></span>
                                </div>


                                <?php if (isset($_SESSION['cart'][$product['id']])): ?>
                                    <ul class="ml-4">
                                        <?php foreach ($_SESSION['cart'][$product['id']] as $size => $quantity): ?>
                                            <?php $all_quantities += $quantity; ?>
                                            <li>
                                                (Size: <?php echo htmlspecialchars($size); ?>) - Quantity: <?php echo htmlspecialchars($quantity); ?>
                                                <span class="float-right">฿<?php echo number_format($product['price'] * $quantity, 2); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr class="my-4">
                    <p class="text-lg font-bold" id="fee">ค่าจัดส่ง: ฿0.00</p>
                    <p class="text-lg font-bold" id="total_price">ราคารวมทั้งหมด: ฿<?php echo number_format($total_price, 2); ?></p>
                <?php else: ?>
                    <p>Your cart is empty.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold mb-4">สรุปคำสั่งซื้อ</h3>
                <form action="checkout.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700">ชื่อ</label>
                        <input type="text" name="name" id="name" required class="w-full p-2 border border-gray-300 rounded-lg" value="<?php echo $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']; ?>">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700">อีเมลล์</label>
                        <input type="email" name="email" id="email" required class="w-full p-2 border border-gray-300 rounded-lg" value="<?php echo $_SESSION['user_email']; ?>">
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700">ที่อยู่จัดส่ง</label>
                        <input type="text" id="address" name="address" class="w-full p-2 border border-gray-300 rounded-lg" required value="<?php echo $_SESSION['user_address']; ?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label for="address_num" class="block text-gray-700">ที่อยู่</label>
                        <input type="text" id="address_num" name="address_num" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="บ้านเลขที่">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                    <div>
                            <label for="province" class="block text-gray-700">จังหวัด</label>
                            <select name="province" id="province" class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกจังหวัด</option>
                            </select>
                        </div>
                        <div>
                            <label for="amphoe" class="block text-gray-700">อำเภอ</label>
                            <select name="amphoe" id="amphoe" class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกอำเภอ</option>
                            </select>
                        </div>


                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="district" class="block text-gray-700">เขต/ตำบล</label>
                            <select name="district" id="district" class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกเขต</option>
                            </select>
                        </div>
                        <div>
                            <label for="zipcode" class="block text-gray-700">รหัสไปรษณีย์</label>
                            <select name="zipcode" id="zipcode" class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="">เลือกรหัสไปรษณีย์</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="phone_num" class="block text-gray-700">เบอร์โทร</label>
                        <input type="text" id="phone_num" name="phone_num" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="เบอร์โทร" value="<?php echo $_SESSION['user_phone']; ?>" maxlength="10">
                    </div>

                    <div class="mb-4">
                        <label for="shipper" class="block text-gray-700">ขนส่ง</label>
                        <select name="shipper" id="shipper" required class="w-full p-2 border border-gray-300 rounded-lg" onchange="UpdateFee()">
                            <?php if ($all_quantities >= 10) { ?>
                            <?php foreach ($shippers as $shipper): ?>
                                <option value="<?php echo $shipper['id']; ?>"><?php echo $shipper['name'] . ' - ฿' . ($shipper['price'] * 2); ?></option>
                            <?php endforeach; ?>
                            <?php } else { ?>
                                <?php foreach ($shippers as $shipper): ?>
                                <option value="<?php echo $shipper['id']; ?>"><?php echo $shipper['name'] . ' - ฿' . $shipper['price']; ?></option>
                            <?php endforeach; ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="payment_method" class="block text-gray-700">ช่องทางชําระเงิน</label>
                        <select name="payment_method" id="payment_method" required class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <img src="public/image/Qr Payment.JPG" alt="" srcset="" class="w-[200px] ml-auto mr-auto">
                    </div>

                    <div class="mb-4">
                        <label for="payment_slip" class="block text-gray-700">อัพโหลดหลักฐานชําระเงิน</label>
                        <input type="file" name="payment_slip" id="payment_slip" required class="w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <button type="submit" name="submit_order" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">ส่งคำสั่งซื้อ</button>
                </form>
            </div>
        </div>
    </div>

    <script>
let addressData = [];

// Load address.json data
fetch('config/address.json')
    .then(response => response.json())
    .then(data => {
        addressData = data;
        populateProvinces(); // Populate provinces on page load
    })
    .catch(error => console.error('Error loading address data:', error));



const addressInput = document.getElementById('address');
const districtInput = document.getElementById('district');
const amphoeInput = document.getElementById('amphoe');
const provinceInput = document.getElementById('province');
const zipcodeInput = document.getElementById('zipcode');

// Function to populate provinces
function populateProvinces() {
    const provinces = [...new Set(addressData.map(item => item.province))];
    provinceInput.innerHTML = '<option value="">เลือกจังหวัด</option>';
    provinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province;
        option.textContent = province;
        provinceInput.appendChild(option);
    });
}

// Function to populate amphoe based on selected province
function populateAmphoe(selectedProvince) {
    const amphoes = [...new Set(addressData.filter(item => item.province === selectedProvince).map(item => item.amphoe))];
    amphoeInput.innerHTML = '<option value="">เลือกอำเภอ</option>';
    amphoes.forEach(amphoe => {
        const option = document.createElement('option');
        option.value = amphoe;
        option.textContent = amphoe;
        amphoeInput.appendChild(option);
    });
    districtInput.innerHTML = '<option value="">เลือกเขต/ตำบล</option>'; // Reset districts
    zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>'; // Reset zipcodes
}

// Function to populate districts based on selected amphoe
function populateDistricts(selectedAmphoe) {
    const districts = [...new Set(addressData.filter(item => item.amphoe === selectedAmphoe).map(item => item.district))];
    districtInput.innerHTML = '<option value="">เลือกเขต</option>';
    districts.forEach(district => {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        districtInput.appendChild(option);
    });
    zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>'; // Reset zipcodes
}

// Function to populate zipcodes based on selected district
function populateZipcodes(selectedDistrict) {
    const zipcodes = [...new Set(addressData.filter(item => item.district === selectedDistrict).map(item => item.zipcode))];
    zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>';
    zipcodes.forEach(zipcode => {
        const option = document.createElement('option');
        option.value = zipcode;
        option.textContent = zipcode;
        zipcodeInput.appendChild(option);
    });
}

// Event listeners for cascading dropdowns
provinceInput.addEventListener('change', function () {
    const selectedProvince = this.value;
    if (selectedProvince) {
        populateAmphoe(selectedProvince);
    } else {
        amphoeInput.innerHTML = '<option value="">เลือกอำเภอ</option>';
        districtInput.innerHTML = '<option value="">เลือกเขต/ตำบล</option>';
        zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>';
    }
});

amphoeInput.addEventListener('change', function () {
    const selectedAmphoe = this.value;
    if (selectedAmphoe) {
        populateDistricts(selectedAmphoe);
    } else {
        districtInput.innerHTML = '<option value="">เลือกเขต/ตำบล</option>';
        zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>';
    }
});

districtInput.addEventListener('change', function () {
    const selectedDistrict = this.value;
    if (selectedDistrict) {
        populateZipcodes(selectedDistrict);
    } else {
        zipcodeInput.innerHTML = '<option value="">เลือกรหัสไปรษณีย์</option>';
    }
});

zipcodeInput.addEventListener('change', function () {
    var address_num_input = document.getElementById('address_num');
    addressInput.value = address_num_input.value + ", " + amphoeInput.value + ", " + districtInput.value + ", " + provinceInput.value + ", " + zipcodeInput.value;
});

</script>

    <script>
        function UpdateFee() {
            var shipper = document.getElementById('shipper').value;

            var fee = 0;

            <?php if ($all_quantities >= 10) { ?>

            fetch('get_shipper.php?id=' + shipper)
                .then(response => response.json())
                .then(data => {
                    fee = data.price;
                    fee = fee * 2;
                    console.log(fee);
                    document.getElementById('fee').innerText = 'ค่าจัดส่ง: ฿' + fee;

                    var total_price = parseFloat(<?php echo $total_price; ?>) + parseFloat(fee);
                    document.getElementById('total_price').innerText = 'ราคารวมทั้งหมด: ฿' + total_price.toFixed(2);
                });

            <?php } else { ?>

                
            fetch('get_shipper.php?id=' + shipper)
                .then(response => response.json())
                .then(data => {
                    fee = data.price;
                    console.log(fee);
                    document.getElementById('fee').innerText = 'ค่าจัดส่ง: ฿' + fee;

                    var total_price = parseFloat(<?php echo $total_price; ?>) + parseFloat(fee);
                    document.getElementById('total_price').innerText = 'ราคารวมทั้งหมด: ฿' + total_price.toFixed(2);
                });

            <?php } ?>
            

        }

        UpdateFee();
    </script>

    <?php include 'components/footer.php'; ?>
</body>

</html>