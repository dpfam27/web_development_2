<?php
/**
 * Email Sending Helper using PHPMailer
 * Hàm gửi email cho khách và contact
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'autoload.php'; // Require PHPMailer
include 'mail_config.php';

/**
 * Gửi email xác nhận đơn hàng cho khách
 * @param string $customer_email - Email của khách
 * @param string $customer_name - Tên của khách
 * @param int $order_id - ID của đơn hàng
 * @param float $total_amount - Tổng tiền
 * @param array $items - Danh sách sản phẩm
 * @return bool
 */
function sendOrderConfirmationEmail($customer_email, $customer_name, $order_id, $total_amount, $items) {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($customer_email, $customer_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đơn hàng #' . $order_id;
        
        // Build HTML email
        $items_html = '';
        foreach ($items as $item) {
            $items_html .= '
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($item['prod_name']) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($item['variant_name']) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">' . $item['quantity'] . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$' . number_format($item['price'], 2) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$' . number_format($item['price'] * $item['quantity'], 2) . '</td>
            </tr>';
        }

        $html_body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; }
                .header { background-color: #004085; color: white; padding: 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 20px; }
                .order-info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .total-row { background-color: #004085; color: white; font-weight: bold; }
                .total-row td { padding: 12px; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .btn { background-color: #004085; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>WheyStore</h1>
                    <p>Cảm ơn bạn đã mua sắm</p>
                </div>
                
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($customer_name) . '</strong>,</p>
                    
                    <p>Cảm ơn bạn đã đặt hàng tại WheyStore! Đơn hàng của bạn đã được xác nhận và đang được xử lý.</p>
                    
                    <div class="order-info">
                        <p><strong>Mã đơn hàng:</strong> #' . $order_id . '</p>
                        <p><strong>Ngày đặt hàng:</strong> ' . date('d/m/Y H:i') . '</p>
                    </div>
                    
                    <h3>Chi tiết đơn hàng:</h3>
                    <table>
                        <thead>
                            <tr style="background-color: #e9ecef;">
                                <th style="padding: 10px; text-align: left;">Sản phẩm</th>
                                <th style="padding: 10px; text-align: left;">Biến thể</th>
                                <th style="padding: 10px; text-align: center;">Số lượng</th>
                                <th style="padding: 10px; text-align: right;">Giá</th>
                                <th style="padding: 10px; text-align: right;">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $items_html . '
                        </tbody>
                    </table>
                    
                    <table>
                        <tr style="background-color: #e9ecef;">
                            <td style="padding: 10px;"><strong>Tổng tiền:</strong></td>
                            <td style="padding: 10px; text-align: right;"><strong>$' . number_format($total_amount, 2) . '</strong></td>
                        </tr>
                    </table>
                    
                    <p>Bạn có thể theo dõi đơn hàng của mình bằng cách <a href="https://yourwebsite.com/my_orders.php" class="btn">Xem đơn hàng của tôi</a></p>
                    
                    <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua support@wheystore.com</p>
                    
                    <p>Cảm ơn bạn đã tin tưởng WheyStore!</p>
                </div>
                
                <div class="footer">
                    <p>&copy; 2025 WheyStore. All rights reserved.</p>
                    <p>Địa chỉ: 123 Protein Street, Fitness City | Điện thoại: +1 (555) 123-4567</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $html_body;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $html_body));

        if ($mail->send()) {
            return true;
        }
        return false;

    } catch (Exception $e) {
        // Ghi log lỗi nhưng không dừng quy trình
        error_log("Lỗi gửi email xác nhận: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email liên hệ từ khách tới support
 * @param string $customer_name - Tên khách
 * @param string $customer_email - Email khách
 * @param string $subject - Tiêu đề
 * @param string $message - Nội dung tin nhắn
 * @return bool
 */
function sendContactEmail($customer_name, $customer_email, $subject, $message) {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients - Gửi tới email support (email cá nhân)
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress(SUPPORT_EMAIL); // Gửi tới support email
        $mail->addReplyTo($customer_email, $customer_name); // Reply tới customer

        // Content
        $mail->isHTML(true);
        $mail->Subject = '[Liên hệ từ trang web] ' . $subject;

        $html_body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; }
                .header { background-color: #004085; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .customer-info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .message-body { background-color: #fff; padding: 15px; border-left: 4px solid #004085; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>WheyStore - Liên hệ từ khách hàng</h1>
                </div>
                
                <div class="content">
                    <h3>Tin nhắn liên hệ mới:</h3>
                    
                    <div class="customer-info">
                        <p><strong>Tên khách:</strong> ' . htmlspecialchars($customer_name) . '</p>
                        <p><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($customer_email) . '">' . htmlspecialchars($customer_email) . '</a></p>
                        <p><strong>Tiêu đề:</strong> ' . htmlspecialchars($subject) . '</p>
                        <p><strong>Thời gian:</strong> ' . date('d/m/Y H:i:s') . '</p>
                    </div>
                    
                    <h4>Nội dung tin nhắn:</h4>
                    <div class="message-body">
                        ' . nl2br(htmlspecialchars($message)) . '
                    </div>
                    
                    <p style="color: #666; font-size: 12px;">
                        <strong>Ghi chú:</strong> Bạn có thể trả lời email này để liên hệ trực tiếp với khách hàng.
                    </p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $html_body;
        $mail->AltBody = "Tin nhắn từ: $customer_name ($customer_email)\n\nTiêu đề: $subject\n\n$message";

        if ($mail->send()) {
            return true;
        }
        return false;

    } catch (Exception $e) {
        error_log("Lỗi gửi email liên hệ: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email phản hồi cho khách sau khi gửi liên hệ
 * @param string $customer_email - Email khách
 * @param string $customer_name - Tên khách
 * @return bool
 */
function sendContactConfirmationEmail($customer_email, $customer_name) {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($customer_email, $customer_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Chúng tôi đã nhận được tin nhắn của bạn - WheyStore';

        $html_body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; }
                .header { background-color: #004085; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>WheyStore</h1>
                </div>
                
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($customer_name) . '</strong>,</p>
                    
                    <p>Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi đã nhận được tin nhắn của bạn và sẽ phản hồi sớm nhất có thể.</p>
                    
                    <p>Thời gian phản hồi thường từ 24-48 giờ làm việc.</p>
                    
                    <p>Nếu vấn đề của bạn khẩn cấp, vui lòng gọi chúng tôi qua số điện thoại: +1 (555) 123-4567</p>
                    
                    <p>Cảm ơn bạn đã tin tưởng WheyStore!</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $html_body;
        $mail->AltBody = "Cảm ơn bạn đã liên hệ với chúng tôi. Chúng tôi sẽ phản hồi sớm nhất có thể.";

        if ($mail->send()) {
            return true;
        }
        return false;

    } catch (Exception $e) {
        error_log("Lỗi gửi email xác nhận liên hệ: " . $mail->ErrorInfo);
        return false;
    }
}

?>
