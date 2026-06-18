# 通企付 PHP SDK

基于通联支付（通企付）的 PHP 开发包，支持微信、支付宝等多种支付方式。

## 安装

```bash
composer require feihu/allinpay-tppay
```

## 基本使用

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use AllinPay\Sdk\TqPay;

$config = [
    'signType' => 'MD5',                    // 签名类型: MD5/RSA/SM2
    'mchNo' => 'YOUR_MCH_NO',               // 商户号
    'appId' => 'YOUR_APP_ID',               // 应用ID
    'md5Key' => 'your_md5_key_here',        // MD5密钥
    'priRsaKey' => 'your_private_key_here', // RSA私钥
    'pubRsaKey' => 'your_public_key_here',  // RSA公钥
    'notifyUrl' => 'https://your-domain.com/notify.php', // 回调地址
];

$tqPay = new TqPay($config);

// 微信主扫下单
$result = $tqPay->wxNative('1', '会员充值'); // 这里的金额单位是分

if ($result['code'] == '0') {
    $payData = $result['payData']; // 支付链接，用于生成二维码
}
```

## 内置支付方式

| wayCode | 说明 | 方法 |
|---------|------|------|
| WX_NATIVE | 微信主扫 | `wxNative()` |
| WX_JSAPI | 微信公众号支付 | `wxJsapi()` |
| ALI_QR | 支付宝主扫 | `aliQr()` |
| H5_CASHIER | H5收银台 | `h5Cashier()` |

## 回调通知处理

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use AllinPay\Sdk\TqPay;

$tqPay = new TqPay($config);

$params = $_POST;

if ($tqPay->verifyNotify($params)) {
    $payOrderId = $params['payOrderId'];
    $mchOrderNo = $params['mchOrderNo'];
    $amount = $params['amount'];
    $state = $params['state'];
    
    if ($state == '2') {
        // 支付成功，处理业务逻辑
    }
    
    echo 'success';
} else {
    echo 'ERROR';
}
```

---

## 扩展机制

SDK 采用插件化设计，支持在不修改核心代码（vendor）的情况下扩展新功能。

### 为什么需要扩展机制

通企付提供了大量 API（29个功能模块），而 SDK 核心仅实现了最常用的支付功能。通过扩展机制，你可以：

1. **按需扩展** - 只实现你需要的功能
2. **安全升级** - 升级 SDK 不会覆盖你的自定义代码
3. **团队协作** - 不同开发者可以独立开发不同扩展

### 扩展架构

```
┌─────────────────────────────────────────────┐
│                  TqPay                      │
│  ┌─────────────┬─────────────┬───────────┐ │
│  │ OrderExt    │AccountExt   │自定义扩展  │ │
│  │ (订单查询)  │(余额查询)   │(你的代码) │ │
│  └─────────────┴─────────────┴───────────┘ │
│           动态调用 (__call)                 │
└─────────────────────────────────────────────┘
```

### 如何创建扩展

#### 步骤1：创建扩展类

```php
<?php

// 在你的项目中创建，不要放在 vendor 目录下
// 示例：app/Extension/MyCustomExtension.php

namespace App\Extension;

use AllinPay\Sdk\Extension\BaseExtension;

class MyCustomExtension extends BaseExtension
{
    /**
     * 获取扩展名称（唯一标识）
     */
    public function getName(): string
    {
        return 'my_custom';
    }

    /**
     * 自定义方法：查询账单
     */
    public function queryBill(string $date): array
    {
        $params = [
            'date' => $date,
        ];

        return $this->request('/bill/queryBill', $params);
    }

    /**
     * 自定义方法：转账
     */
    public function transfer(array $params): array
    {
        return $this->request('/transfer/transferApply', $params);
    }
}
```

#### 步骤2：注册扩展

```php
<?php

use AllinPay\Sdk\TqPay;
use App\Extension\MyCustomExtension;

// 方式1：初始化时注册
$tqPay = new TqPay($config, [
    new MyCustomExtension(),
]);

// 方式2：动态添加
$tqPay = new TqPay($config);
$tqPay->addExtension(new MyCustomExtension());

// 方式3：通过类名添加（自动实例化）
$tqPay->addExtension(MyCustomExtension::class);
```

#### 步骤3：使用扩展方法

```php
// 直接通过 TqPay 实例调用扩展方法
$result = $tqPay->queryBill('20260618');

// 或者先获取扩展，再调用方法
$extension = $tqPay->getExtension('my_custom');
$result = $extension->transfer($params);
```

### 扩展基类提供的方法

`BaseExtension` 提供了以下便捷方法：

| 方法 | 说明 |
|------|------|
| `request($path, $params)` | 发送 API 请求（自动签名、验签） |
| `buildBaseParams($params)` | 构建基础参数（mchNo、appId、signType等） |
| `parseResponse($response)` | 解析响应（自动验签） |
| `getConfig()` | 获取配置 |
| `getSignKit()` | 获取签名工具 |
| `getTqPay()` | 获取 TqPay 实例 |

### 内置扩展

SDK 内置了以下扩展，可直接使用：

#### OrderExtension（订单扩展）

```php
use AllinPay\Sdk\Extension\OrderExtension;

$tqPay->addExtension(new OrderExtension());

// 查询支付订单
$result = $tqPay->queryPayOrder('P2067422381146746881');

// 关闭支付订单
$result = $tqPay->closePayOrder('P2067422381146746881');

// 统一退款
$result = $tqPay->refundOrder(
    'P2067422381146746881',
    '1.00',
    'REF' . date('YmdHis')
);

// 查询退款订单
$result = $tqPay->queryRefundOrder('REF20260618094120');
```

#### AccountExtension（账户扩展）

```php
use AllinPay\Sdk\Extension\AccountExtension;

$tqPay->addExtension(new AccountExtension());

// 查询余额
$result = $tqPay->queryBalance();

// 提现申请
$result = $tqPay->withdrawApply(
    '100.00',
    'WITHDRAW' . date('YmdHis'),
    '6222021234567890123',
    '张三',
    '0102',
    '工商银行',
    '北京市',
    '北京市',
    '北京分行'
);
```

#### SettlementExtension（分账扩展）

```php
use AllinPay\Sdk\Extension\SettlementExtension;

$tqPay->addExtension(new SettlementExtension());

// 批量分账
$result = $tqPay->settlementApply(
    'P2067422381146746881',
    [
        [
            'receiptNo' => 'RECEIVER001',
            'amount' => '5000',
            'remark' => '分账给商户A',
        ],
    ]
);

// 查询分账订单
$result = $tqPay->querySettlementOrder('SETTLE20260618094120');

// 分账撤销
$result = $tqPay->settlementCancel('SETTLE20260618094120');
```

### 扩展管理方法

```php
// 检查扩展是否已注册
$tqPay->hasExtension('order'); // bool

// 获取所有已注册的扩展名称
$tqPay->getExtensionNames(); // array

// 获取扩展实例
$extension = $tqPay->getExtension('order'); // ExtensionInterface|null
```

### 扩展开发最佳实践

1. **不要修改 vendor 代码** - 所有自定义扩展应放在你的项目目录中
2. **遵循命名规范** - 扩展类名以 `Extension` 结尾，如 `BillExtension`
3. **单一职责** - 每个扩展只负责一个功能模块
4. **提供文档** - 为你的扩展方法添加 PHPDoc 注释
5. **错误处理** - 在扩展中捕获异常并返回统一格式

---

## 订单状态说明

| 状态码 | 说明 |
|-------|------|
| 0 | 订单生成 |
| 1 | 支付中 |
| 2 | 支付成功 |
| 3 | 支付失败 |
| 5 | 已退款 |
| 6 | 订单关闭 |
| 7 | 预消费支付成功 |

## 金额说明

- 接口金额单位为**分**
- SDK 方法参数单位为**元**，会自动转换

## License

MIT
