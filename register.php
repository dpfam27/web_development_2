<?php
include 'connect.php'; 

$message = ""; 

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($link, trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = '<div class="alert alert-danger">Vui lòng nhập Username và Password.</div>';
    } else {
        // Kiểm tra tên người dùng đã tồn tại
        $sql_check = "SELECT id FROM users WHERE username = '$username'";
        $result_check = mysqli_query($link, $sql_check);

        if (mysqli_num_rows($result_check) > 0) {
            $message = '<div class="alert alert-warning">Tên người dùng **' . htmlspecialchars($username) . '** đã tồn tại.</div>';
        } else {
                // Mã hóa mật khẩu bằng MD5
                $hashed_password = md5($password);

                // Kiểm tra xem bảng users có cột `role` hay không
                $has_role = false;
                $check_col = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'role'");
                if ($check_col && mysqli_num_rows($check_col) > 0) {
                    $has_role = true;
                }

                // Thêm người dùng mới — nếu có cột role thì chèn role='user', nếu không thì chèn 2 cột
                if ($has_role) {
                    $sql_insert = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'user')";
                } else {
                    $sql_insert = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
                }

                if (mysqli_query($link, $sql_insert)) {
                $message = '<div class="alert alert-success">Đăng ký thành công! Bạn có thể <a href="login.php">Đăng nhập</a>.</div>';
            } else {
                $message = '<div class="alert alert-danger">Lỗi hệ thống: ' . mysqli_error($link) . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Basic (No Session)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper { width: 360px; padding: 20px; margin: 50px auto; border: 1px solid #ccc; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="mb-4">Đăng Ký Cơ Bản</h2>
        
        <?php echo $message; // Hiển thị thông báo ?>
        
        <form action="register.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" name="register" class="btn btn-primary btn-block">Đăng Ký</button>

            <p class="text-center mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập</a>.</p>
        </form>
    </div>    
</body>
</html>