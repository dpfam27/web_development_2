<?php
include 'connect.php';

if (isset($_POST['keyword'])) {
    $keyword = mysqli_real_escape_string($link, $_POST['keyword']);
    
    // Chỉ tìm kiếm nếu từ khóa dài hơn 1 ký tự
    if (strlen($keyword) > 1) {
        $sql = "SELECT product_id, name, image_url, base_price FROM products WHERE name LIKE '%$keyword%' LIMIT 5";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo '<ul class="list-group">';
            while ($row = mysqli_fetch_assoc($result)) {
                $img = !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/50';
                // Khi click vào item gợi ý thì chuyển sang trang chi tiết luôn
                echo '<li class="list-group-item list-group-item-action d-flex align-items-center" onclick="window.location=\'product_detail.php?id='.$row['product_id'].'\'" style="cursor: pointer;">
                        <img src="'.$img.'" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">'.$row['name'].'</div>
                            <div style="font-size: 12px; color: #dc3545;">$'.number_format($row['base_price'], 2).'</div>
                        </div>
                      </li>';
            }
            echo '</ul>';
        } else {
            echo '<ul class="list-group"><li class="list-group-item">Không tìm thấy sản phẩm nào.</li></ul>';
        }
    }
}
?>