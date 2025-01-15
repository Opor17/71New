<?php
try {
    session_start();
    include 'config/db.php'; // Include the database connection

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Update user details if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_firstname = $_POST['user_firstname'];
        $user_lastname = $_POST['user_lastname'];
        $user_phone = $_POST['user_phone'];
        $user_address = $_POST['user_address'];
        $user_birthday = $_POST['user_birthday'];
        $user_gender = $_POST['user_gender'];

        $update_sql = "UPDATE users SET 
                            user_firstname = :firstname, 
                            user_lastname = :lastname, 
                            user_phone = :phone, 
                            user_address = :address, 
                            user_birthday = :birthday, 
                            user_gender = :gender 
                       WHERE user_id = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':firstname', $user_firstname);
        $update_stmt->bindParam(':lastname', $user_lastname);
        $update_stmt->bindParam(':phone', $user_phone);
        $update_stmt->bindParam(':address', $user_address);
        $update_stmt->bindParam(':birthday', $user_birthday);
        $update_stmt->bindParam(':gender', $user_gender);
        $update_stmt->bindParam(':id', $_SESSION['user_id']);
        $update_stmt->execute();

        $_SESSION['user_address'] = $user_address;
        $_SESSION['user_firstname'] = $user_firstname;
        $_SESSION['user_lastname'] = $user_lastname;
        // Reload user data after update
        $msg = "Profile updated successfully!";
    }

    // Fetch user details
    $sql = "SELECT * FROM users WHERE user_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h1>User not found!</h1>";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Profile Edit Section -->
    <section class="container mx-auto py-12">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div>
                <?php include 'components/menu_profile.php'; ?>
            </div>
            <div class="col-span-3">
                <div class="border border-gray-300 p-4 ">
                    <form method="post">
                        <h2 class="text-2xl font-bold mb-4">แก้ไขบัญชี</h2>
                        <?php if (isset($msg)) echo "<p class='text-green-500 mb-4'>$msg</p>"; ?>
                        
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">ชื่อ</h3>
                                </div>
                                <div>
                                    <input type="text" value="<?php echo htmlspecialchars($user['user_firstname']); ?>" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="user_firstname">
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">นามสกุล</h3>
                                </div>
                                <div>
                                    <input type="text" value="<?php echo htmlspecialchars($user['user_lastname']); ?>" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="user_lastname">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">โทรศัพท์</h3>
                                </div>
                                <div>
                                    <input type="text" value="<?php echo htmlspecialchars($user['user_phone']); ?>" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="user_phone" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">ข้อมูลที่อยู่</h3>
                                </div>
                                <div>
                                    <input type="text" value="<?php echo htmlspecialchars($user['user_address']); ?>" class="bg-[#D9D9D9] w-[426px] h-[39px]" id="user_address" name="user_address" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">ที่อยู่</h3>
                                </div>
                                <div>
                                    <input type="text" id="address_num" name="address_num" class="bg-[#D9D9D9] w-[426px] h-[39px]" placeholder="บ้านเลขที่">
                                </div>
                            </div>
                        </div>

                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">จังหวัด</h3>
                                </div>
                                <div>
                                    <select name="province" id="province" class="bg-[#D9D9D9] w-[426px] h-[39px]">
                                        <option value="">เลือกจังหวัด</option>
                                    </select>
                                </div>
                        </div>
                        <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">อำเภอ</h3>
                                </div>
                                <div>
                                <select name="amphoe" id="amphoe" class="bg-[#D9D9D9] w-[426px] h-[39px]">
                                    <option value="">เลือกอำเภอ</option>
                                </select>
                                </div>
                        </div>


                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-2">
                    <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">เขต/ตำบล</h3>
                                </div>
                                <div>
                                <select name="district" id="district" class="bg-[#D9D9D9] w-[426px] h-[39px]">
                                    <option value="">เลือกเขต/ตำบล</option>
                                </select>
                                </div>
                        </div>
                        <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">เลือกรหัสไปรษณีย์</h3>
                                </div>
                                <div>
                                <select name="zipcode" id="zipcode" class="bg-[#D9D9D9] w-[426px] h-[39px]">
                                    <option value="">เลือกรหัสไปรษณีย์</option>
                                </select>
                                </div>
                        </div>
                    </div>


                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] items-center flex">
                                    <h3 class="text-lg font-bold">วันเกิด</h3>
                                </div>
                                <div>
                                    <input type="date" value="<?php echo htmlspecialchars($user['user_birthday']); ?>" class="bg-[#D9D9D9] w-[426px] h-[39px]" name="user_birthday">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="flex">
                                <div class="w-[300px] flex items-center">
                                    <h3 class="text-lg font-bold">เพศ</h3>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="user_gender" value="ชาย"
                                            <?php echo ($user['user_gender'] === 'ชาย') ? 'checked' : ''; ?>
                                            class="form-radio text-blue-500">
                                        <span class="ml-2">ชาย</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="user_gender" value="หญิง"
                                            <?php echo ($user['user_gender'] === 'หญิง') ? 'checked' : ''; ?>
                                            class="form-radio text-blue-500">
                                        <span class="ml-2">หญิง</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="inline-block px-4 py-2 leading-none border rounded text-white bg-[#1E1E1E] mt-10 lg:mt-0 hover:bg-gray-800 transition duration-200">
                                บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

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



const addressInput = document.getElementById('user_address');
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

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
</body>


</html>

<?php
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
