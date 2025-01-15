<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก | Bootleg Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-white">
    <?php include 'components/navbar.php';?>

    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">สมัครสมาชิก</h2>
            <form method="POST" action="register.php" onsubmit="return validatePassword()">

                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">อีเมลล์</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="user_firstname" class="block text-gray-700 text-sm font-bold mb-2">ชื่อ</label>
                    <input type="text" id="user_firstname" name="user_firstname" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="user_lastname" class="block text-gray-700 text-sm font-bold mb-2">นามสกุล</label>
                    <input type="text" id="user_lastname" name="user_lastname" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="user_phone" class="block text-gray-700 text-sm font-bold mb-2">เบอร์โทร</label>
                    <input type="text" id="user_phone" name="user_phone" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="password" name="password_confirm" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <button type="submit" name="register"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none">
                        สมัครสมาชิก
                    </button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                มีบัญชีแล้ว? <a href="login.php" class="text-blue-600 hover:underline">เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>

    <script>
    function validatePassword() {
        const password = document.getElementById('password').value;
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        
        if (!passwordPattern.test(password)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid password',
                text: 'รหัสผ่านต้องมีความยาว 8-20 ตัวอักษร และประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็กและตัวเลข',
            });
            return false; // Prevent form submission
        }
        return true;
    }
</script>

    <?php

    if (isset($_POST['register'])) {
        try{
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $user_firstname = $_POST['user_firstname'];
        $user_lastname = $_POST['user_lastname'];
        $user_phone = $_POST['user_phone'];

        
        include 'config/db.php';
        // Dummy registration process (replace with real database insert)
        // Assuming the user registers successfully
    
    
        $sql_select = "SELECT * FROM users WHERE user_email = '$email'";
        $stmt = $conn->prepare($sql_select);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Email already exists!',
                    });
                </script>
            ";
            exit();
        } else {
            $sql_insert = "INSERT INTO users (user_username, user_email, user_firstname, user_lastname, user_password, user_phone, user_rule) VALUES ('$username', '$email', '$user_firstname', '$user_lastname', '$password', '$user_phone', 'user')";
            $stmt = $conn->prepare($sql_insert);
            $stmt->execute();
    
        }
    
        echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registration successful!',
                    text: 'You can now log in.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location = 'login.php';
                });
            </script>
        ";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    }
    ?>
</body>
</html>
