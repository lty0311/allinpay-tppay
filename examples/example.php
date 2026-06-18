<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AllinPay\Sdk\TqPay;

// 配置参数
$config = [
    'signType' => 'MD5',                    // 签名类型: MD5/RSA/SM2
    'mchNo' => 'E249974037860',              // 商户号
    'appId' => '66384647e4b0148f4bb07829',   // 应用ID
    // MD5密钥
    'md5Key' => 'r7yu2j8f1bjtv9d57c47y8bpobwsfj6g8ywktyvkilj40uxjgxarrocvbue2lg4d6wciropth06c8k35pywtn085v7umo0f0qnk63m92c0z0xheqlncy73z1ofrxchwi',  // MD5密钥
    // 生产联调商户RSA私钥
    'priRsaKey' => 'MIIEwAIBADANBgkqhkiG9w0BAQEFAASCBKowggSmAgEAAoIBAQDNCH60TTVuLrDaj0FezKvIN23jYIiWH0Fp6RGmdGSVF5i+ASA+hxVb35g887QXVrCy2/61EoDBwgm3XwGO9yxXBIhMH8yahBvcgnJ+kIwPV+3MTvKReTPXH3l5nSVkH4UMtysgsvpC4cNQXQCAihV1X9oYk4uWNrtIw22l7OsurGRqwGq6qogn68hmgoVmox5ZajLWacZegCKyRbCGw+r9bHkrPhmewgUT0iWntB5n7ssTCrpLNbufkr4CLBl3lQCRTGxZgBwu5D1QNQ7EgivpOWS6YU6uwvNn8edSWqt2npUv9F9a/jBno3Ztjg4McRxMShPsr9ZmyLa2Dn3acTu3AgMBAAECggEBAL1gzE9TRTe5LqchTJr4Zu6uq/Qp9N7cjbn6ic/6j/DZ874EIjk+4i6S7vXVj5FDBECgTLoJFh5hUEIsIa9ghHb3e8D8WiqaPXXGk2RttMBzUfS1Mv3FOmnH65a13JSHPIhLIy23wspF5vZygIU5hap9V1/94B9l/ESwJbXtiCj5cSiDK2riqlwhm8Mmy/taFharaKdhI7/ajRA3t7LmKv55T1jnLP46mymeCnnvQR8hAWNzKksHwVK6s6sbihPlbsJt+PODMElZwloOkOqX7+FNew1JCdljVQjuj8FOr3nvuCr4rc6ZGfZv64D7Vpku69e4jBYdYgzIwpheLE+Pl2kCgYEA5R4qQYi94LjFfSV2JdaYnIsjtmnqs1vYgiGmpMDTyOC5ltKZEVsLpArnRRGiyZ6yIFMnxnj2IswWNkp3uVuU5G8dzPSHJhoeRMBo18PbnHm6eEPWkCCUVmXLv83sk6IamPQfktJ6CsJyhauW2i3ZuiYQWz0K91Y3kl6P1ST8fDsCgYEA5Rbq/QwXL1718oY/J955Pn8YlypQ6+u8bR+HFH2xYeFFK1+ZbIFOg9S2b6KzfugB4nL3IWso47NDzKGttryDgVUK2e+PONKO4kvZYVF3D7C8j7C4ppDvOLkBF4TpCcHeX5StQgD2F+++AaqA1g60Hrja6Pvc8tBbfFyIWFK/0rUCgYEA2YO7xh+VdCS2iMrp0Z5boDKQIvuG7+RANbrQxBS0ez6tsrwYyVtQP9qCGRZXH2Zj4UBQwKHRutNaOwyTgQuq3PuIpS14qPmextKMNlsgwPrnxolQx9/GNAfMWTmcYcRBz/fjibX8Wv6jTrfKLTPeQlUkrhnTsWDOvUy87DOC6EUCgYEAii4aq2thiLc0joafDYNJioLK6FMj4EmerAt6RMfT+IASYqkVN7d/DlF9gTSYJBH5IQfLPKMQVNfKK2HSEAkBIT+UfgPbVaDbgm/RvLuQnywxcFJd7ko5oPMmT3NhxPrlEK3zG37M8X8wEn0vnO3dgzJpCGLy0JsQs6B0tSGkhokCgYEA0aJHS85KhPf/QOyAuxxZ2EyJHYQw8bDZt24htuvXre2McDL8CATcv72rxrRub7OeL5fvsnIVKP9UVj4N2ahzTh4gyQWivK14hgbq0DhIgmlPnX2PG1YoWqo7O4Qzi2mUv0AoehntmeZGIT4sBH06RWBHvx/6lvcH3bjY54lemUk=',
    // 生产通企付RSA公钥
    'pubRsaKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhL54Juhy3A2czrhMiw4d156Pn5/1XjvnW09P/s3stIeO4NT6BLaqQh28t8TvwiT7/BBj27d1AcV6r2y0f8/cYgwUDncegd9GneN0g9Im2mI/Ybo1vAUnYk7dkS9keL+1FVi2s6tjhVqSL9jpthnvtdO5sA1xRlXY+psBtv9Zh3x4Te8hwsJIPpMV328pdUpSwGrD5pu6IxqXrLjhV5/PkmVsg+v383BK0dg6nIhYbJRe7b8587migk04g6Qek1c8U85A0tOcLTM4IxHZ6GzWMrPeQUjI8KMlvpISOUQThJM2MTd+4KMmbty0vjzPAwYopVDmzdpOvT6qlVBFwtfDJQIDAQAB',
    
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
$result = $tqPay->wxNative('1.00', '会员充值', $mchOrderNo); // 这里的金额单位是分
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
    'amount' => '1',             // 金额（分）
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
