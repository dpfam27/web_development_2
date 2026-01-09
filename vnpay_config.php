<?php
// Set timezone to Vietnam (GMT+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// VNPay Configuration
define('VNPAY_TMN_CODE', 'TJDQM94E'); // Thay bằng TMN Code của bạn
define('VNPAY_HASH_SECRET', 'UT6H6T31OTPFKTFXJ3XTXIK0223RQAXN'); // Thay bằng Hash Secret của bạn
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'); // URL sandbox cho test
define('VNPAY_RETURN_URL', 'http://localhost/web2/vnpay_return.php'); // URL trả về sau thanh toán
define('VNPAY_IPN_URL', 'http://localhost/web2/vnpay_ipn.php'); // URL IPN
define('USD_TO_VND_RATE', 26000); // Tỷ giá USD sang VND

// Hàm tạo URL thanh toán VNPay
function createVNPayUrl($order_id, $amount_usd, $order_info) {
    $vnp_TmnCode = VNPAY_TMN_CODE;
    $vnp_HashSecret = VNPAY_HASH_SECRET;
    $vnp_Url = VNPAY_URL;
    $vnp_Returnurl = VNPAY_RETURN_URL;

    $vnp_TxnRef = $order_id; // Mã đơn hàng
    $vnp_OrderInfo = $order_info;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = round($amount_usd * USD_TO_VND_RATE) * 100; // Chuyển USD sang VND rồi nhân 100
    $vnp_Locale = 'vn';
    $vnp_BankCode = '';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    );

    if (isset($vnp_BankCode) && $vnp_BankCode != "") {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }

    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_Url = $vnp_Url . "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }

    return $vnp_Url;
}

// Hàm xác thực chữ ký VNPay
function validateVNPaySignature($inputData, $vnp_SecureHash) {
    $vnp_HashSecret = VNPAY_HASH_SECRET;

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);

    $hashData = "";
    foreach ($inputData as $key => $value) {
        $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    $hashData = rtrim($hashData, '&');

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    return $secureHash === $vnp_SecureHash;
}
?>