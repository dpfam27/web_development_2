<?php
// BƯỚC 1: KẾT NỐI DATABASE VÀ KHỞI ĐỘNG SESSION
include 'connect.php'; 

// Nếu người dùng đã đăng nhập, chuyển hướng họ đi ngay lập tức
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$message = ""; // Khởi tạo biến lưu thông báo lỗi

// BƯỚC 2: KIỂM TRA NGƯỜI DÙNG CÓ NHẤN NÚT 'login' KHÔNG
if (isset($_POST['login'])) {
    // Lấy dữ liệu từ form
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Mã hóa mật khẩu bằng MD5
    $hashed_password = md5($password); 

    // CHUẨN BỊ CÂU LỆNH SQL (Lấy cả username để lưu vào session)
    $username_db = mysqli_real_escape_string($link, $username); 
    $query = "SELECT id, username, role FROM users WHERE username='$username_db' AND password='$hashed_password'";
    
    // THỰC THI TRUY VẤN
    $result = mysqli_query($link, $query);

    // BƯỚC 3: KIỂM TRA KẾT QUẢ
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // THIẾT LẬP SESSION ĐỂ GHI NHỚ TRẠNG THÁI ĐĂNG NHẬP
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $row['id'];
        $_SESSION["username"] = $row['username']; // Lưu tên người dùng
        $_SESSION["role"] = $row['role']; // Lưu role vào session
        
        // CHUYỂN HƯỚNG ĐẾN TRANG CHỦ
        header("Location: index.php");
        exit; 
    } else {
        // Đăng nhập thất bại
        $message = '<div class="alert alert-danger">Wrong info (Thông tin không đúng).</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Basic</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper { width: 360px; padding: 20px; margin: 50px auto; border: 1px solid #ccc; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="mb-4">Đăng Nhập Quản Trị</h2>
        
        <?php echo $message; // Chỉ hiển thị khi có lỗi đăng nhập ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label>User name</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>

            <p class="text-center mt-3">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>.</p>
        </form>
    </div>    
</body>
</html>