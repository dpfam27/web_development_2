<?php
include 'connect.php';
include 'auth.php'; // Chứa session_start

// Nếu đã đăng nhập thì đá về trang chủ
if (is_logged_in()) { header("Location: index.php"); exit; }

$msg = "";

if (isset($_POST['login'])) {
    $input = mysqli_real_escape_string($link, $_POST['username']); // Chấp nhận cả Username hoặc Email
    $password_input = $_POST['password']; // Mật khẩu thô người dùng nhập

    // 1. Tìm user trong database
    $sql = "SELECT * FROM users WHERE username='$input' OR email='$input'";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 2. KIỂM TRA MẬT KHẨU (BƯỚC QUAN TRỌNG)
        // So sánh password nhập vào với hash trong DB
        if (password_verify($password_input, $row['password_hash'])) {
            
            // Mật khẩu đúng -> Lưu Session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Xử lý Cookie ghi nhớ (nếu bạn vẫn dùng code cookie cũ)
            if (isset($_POST['remember'])) {
                include_once 'cookie.php';
                $token = bin2hex(random_bytes(32));
                mysqli_query($link, "UPDATE users SET remember_token='$token' WHERE user_id=".$row['user_id']);
                set_my_cookie('remember_token', $token, 30);
            }

            header("Location: index.php");
            exit;
        } else {
            $msg = "Incorrect password!";
        }
    } else {
        $msg = "Account does not exist!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Log in</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5" style="max-width: 400px;">
    <h3 class="text-center">Log in</h3>
    <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Username or Email</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="rem">
            <label class="form-check-label" for="rem">Remember</label>
        </div>

        <button type="submit" name="login" class="btn btn-primary btn-block">Log in</button>
        <p class="text-center mt-3"><a href="register.php">Sign up</a></p>
    </form>
</body>
</html>
