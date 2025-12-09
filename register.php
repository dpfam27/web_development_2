<?php
include 'connect.php'; 

$msg = ""; 

if (isset($_POST['register'])) {
    // 1. Lấy dữ liệu và làm sạch
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = $_POST['password'];

    // 2. Kiểm tra user đã tồn tại chưa
    $check = mysqli_query($link, "SELECT user_id FROM users WHERE username='$username' OR email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        $msg = "<div class='alert alert-danger'>Username hoặc Email đã tồn tại!</div>";
    } else {
        // 3. MÃ HÓA MẬT KHẨU (QUAN TRỌNG)
        // Hàm này tự động tạo 'salt' và mã hóa an toàn
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 4. Lưu vào Database
        $sql = "INSERT INTO users (username, email, password_hash, role) 
                VALUES ('$username', '$email', '$hashed_password', 'user')";

        if (mysqli_query($link, $sql)) {
            $msg = "<div class='alert alert-success'>Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a></div>";
        } else {
            $msg = "<div class='alert alert-danger'>Lỗi: " . mysqli_error($link) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5" style="max-width: 400px;">
    <h3 class="text-center">Đăng Ký Tài Khoản</h3>
    <?php echo $msg; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary btn-block">Đăng Ký</button>
        <p class="text-center mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </form>
</body>
</html>