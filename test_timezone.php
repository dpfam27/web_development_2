<?php
include 'vnpay_config.php';

echo "Current timezone: " . date_default_timezone_get() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "VNPay CreateDate format: " . date('YmdHis') . "<br>";
echo "Expected Vietnam time: Should be around 2026-01-02 with GMT+7";
?>
