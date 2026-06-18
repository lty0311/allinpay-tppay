<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AllinPay\Sdk\TqPay;

// 配置参数
$config = [
    'signType' => 'MD5',                    // 签名类型: MD5/RSA/SM2
    'mchNo' => 'E249974037860',              // 商户号
    'appId' => '66384647e4b0148f4bb07829',   // 应用ID
    'md5Key' => 'your_md5_key_here',        // MD5密钥
    'priRsaKey' => 'your_private_key_here', // RSA私钥
    'pubRsaKey' => 'your_public_key_here',  // RSA公钥
    'notifyUrl' => 'https://your-domain.com/notify.php', // 回调地址
];

// 初始化SDK
$tqPay = new TqPay($config);

function generateOrderNo($prefix = 'TF')
{
    // 时间戳：14位
    $time = date('YmdHis');
    // 高强度随机数：6位（比 mt_rand 安全）
    $random = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    // 最终单号：2 + 14 + 6 = 20 位（远小于微信 32 位限制）
    $outBillNo = $prefix . $time . $random;
    return $outBillNo;
}

$mchOrderNo = generateOrderNo();
// ========== 示例1: 微信主扫（NATIVE） ==========
$result = $tqPay->wxNative('1.00', '会员充值', $mchOrderNo);
if ($result['code'] == '0') {
    echo "下单成功！\n";
    echo "商户订单号: " . $result['mchOrderNo'] . "\n";
    echo "通企付订单号: " . $result['payOrderId'] . "\n";
    echo "支付链接: " . $result['payData'] . "\n";
    // 使用 $result['payData'] 生成二维码
} else {
    echo "下单失败: " . $result['msg'] . "\n";
}

// ========== 示例2: 通用统一下单 ==========
$result = $tqPay->unifiedOrder([
    'wayCode' => 'WX_NATIVE',       // 支付方式
    'amount' => '1.00',             // 金额（元）
    'body' => '商品充值',           // 商品描述
    'mchOrderNo' => $mchOrderNo, // 商户订单号（可选）
    'clientIp' => '127.0.0.1',      // 客户端IP（可选）
    'extParam' => 'ext info',       // 扩展参数（可选）
]);

// ========== 示例3: 支付宝主扫 ==========
$result = $tqPay->aliQr('1.00', '商品充值');

// ========== 示例4: H5收银台 ==========
$result = $tqPay->h5Cashier('1.00', '商品充值');

// ========== 回调通知处理示例 (notify.php) ==========
/*
$config = require 'config.php';
$tqPay = new TqPay($config);

$params = $_POST;

// 验证签名
if ($tqPay->verifyNotify($params)) {
    $payOrderId = $params['payOrderId'];
    $mchOrderNo = $params['mchOrderNo'];
    $amount = $params['amount'];
    $state = $params['state'];
    
    if ($state == '2') {
        // 支付成功，处理你的业务逻辑
        // 如：更新订单状态、增加用户余额等
        
        // 处理完成后返回 success
        echo 'success';
    } else {
        echo 'success';
    }
} else {
    echo 'ERROR';
}
*/

// ========== 支持的支付方式 (wayCode) ==========
/*
WX_NATIVE     - 微信主扫
WX_JSAPI      - 微信公众号支付（需openid）
WX_LITE       - 微信小程序支付（需openid）
WX_TRANS      - 微信预消费
ALI_QR        - 支付宝主扫
ALI_JSAPI     - 支付宝公众号支付
H5_CASHIER    - H5收银台（支持微信、支付宝、云闪付）
AUTO_BAR      - 付款码支付（被扫）
QUICK_PAY     - 快捷支付
JDBT_PAY      - 白条分期
YT_PAY        - 云梯支付
YW_PAY        - 云微支付
GATEWAY_SDK   - SDK网关支付
*/
