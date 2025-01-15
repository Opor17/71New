<?php
session_start();

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>เข้าสู่ระบบ | Bootleg Store</title>
</head>
<body class="bg-white">
    <?php include 'components/navbar.php'; ?>

    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">เข้าสู่ระบบ</h2>
            <form method="POST" action="login.php">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">อีเมลล์</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6 text-right">
                    <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">ลืมรหัสผ่าน?</a>
                </div>
                <div>
                    <button type="submit" name="login"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none">
                        เข้าสู่ระบบ
                    </button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                ยังไม่มีบัญชี? <a href="register.php" class="text-blue-600 hover:underline">สมัครสมาชิก</a>
            </p>
        </div>
    </div>

    <?php

    if(isset($_SESSION['success'])) {
        echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Password updated successfully!',
                });
            </script>
        ";
        unset($_SESSION['success']);
    }


    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
    
    
        include 'config/db.php';
        // Dummy login check (replace with real database check)
    
        $sql_select = "SELECT * FROM users WHERE user_email = '$email' AND user_password = '$password' AND avaliable = 'true'";
        $stmt = $conn->prepare($sql_select);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
        
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_username'] = $user['user_username'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_rule'] = $user['user_rule'];
            $_SESSION['user_address'] = $user['user_address'];
            $_SESSION['user_firstname'] = $user['user_firstname'];
            $_SESSION['user_lastname'] = $user['user_lastname'];
            $_SESSION['user_phone'] = $user['user_phone'];

            $user_id = $user['user_id'];
    
            $sql_insert_user_login = "INSERT INTO user_login (user_id) VALUE ('$user_id')";
            $stmt = $conn->prepare($sql_insert_user_login);
            $stmt->execute();
            
            echo "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Login successful!',
                        text: 'You will be redirected to the home page.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location = 'index.php';
                    });
                </script>
            ";
        } else {
            echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'ขออภัย ข้อมูลที่กรอกไม่ตรงกับข้อมูลที่บันทึกไว้ กรุณาตรวจสอบตัวสะกดและลองใหม่อีกครั้ง',
                    });
                </script>
            ";
        }
    }
    ?>
</body>
</html>
