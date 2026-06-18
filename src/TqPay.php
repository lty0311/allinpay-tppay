<?php

namespace AllinPay\Sdk;

use AllinPay\Sdk\Util\TqPayKit;
use AllinPay\Sdk\Extension\ExtensionInterface;
use Exception;

/**
 * 通企付支付SDK
 * 
 * 支持扩展机制，允许用户在不修改核心代码的情况下添加新功能
 */
class TqPay
{
    /**
     * 生产环境API地址
     */
    const BASE_URL_PRO = 'https://tp.allinpay.com/pay/api';

    /**
     * 沙箱环境API地址
     */
    const BASE_URL_SANDBOX = 'https://totest.allinpay.com/pay/api';

    /**
     * @var array 配置参数
     */
    private $config;

    /**
     * @var TqPayKit 签名工具
     */
    private $tqPayKit;

    /**
     * @var array 已注册的扩展
     */
    private $extensions = [];

    /**
     * @var array 扩展实例缓存
     */
    private $extensionInstances = [];

    /**
     * @param array $config 配置参数
     * @param array $extensions 扩展列表（可选）
     */
    public function __construct(array $config, array $extensions = [])
    {
        $this->config = array_merge([
            'signType' => 'MD5',
            'baseUrl' => self::BASE_URL_PRO,
            'mchNo' => '',
            'appId' => '',
            'md5Key' => '',
            'priRsaKey' => '',
            'pubRsaKey' => '',
            'priSm2Key' => '',
            'pubSm2Key' => '',
            'memAesKey' => '',
            'notifyUrl' => '',
        ], $config);

        $this->tqPayKit = new TqPayKit($this->config);

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * 获取配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 获取签名工具
     */
    public function getSignKit(): TqPayKit
    {
        return $this->tqPayKit;
    }

    /**
     * 添加扩展
     *
     * @param ExtensionInterface|string $extension 扩展类或类名
     * @return $this
     */
    public function addExtension($extension): self
    {
        if (is_string($extension)) {
            $extension = new $extension();
        }

        if ($extension instanceof ExtensionInterface) {
            $name = $extension->getName();
            $this->extensions[$name] = $extension;
            $extension->initialize($this);
        }

        return $this;
    }

    /**
     * 获取扩展
     *
     * @param string $name 扩展名称
     * @return ExtensionInterface|null
     */
    public function getExtension(string $name): ?ExtensionInterface
    {
        return $this->extensions[$name] ?? null;
    }

    /**
     * 检查扩展是否已注册
     *
     * @param string $name 扩展名称
     * @return bool
     */
    public function hasExtension(string $name): bool
    {
        return isset($this->extensions[$name]);
    }

    /**
     * 获取所有已注册的扩展名称
     *
     * @return array
     */
    public function getExtensionNames(): array
    {
        return array_keys($this->extensions);
    }

    /**
     * 动态调用扩展方法
     *
     * @param string $name 方法名
     * @param array $arguments 参数
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $name)) {
                return call_user_func_array([$extension, $name], $arguments);
            }
        }

        throw new Exception("Method {$name} not found in any extension");
    }

    /**
     * 统一下单
     *
     * @param array $params 下单参数
     * @return array
     */
    public function unifiedOrder(array $params): array
    {
        $mchOrderNo = $params['mchOrderNo'] ?? date('YmdHis') . mt_rand(100000, 999999);

        $requestParams = [
            'appId' => $this->config['appId'],
            'signType' => $this->config['signType'],
            'mchNo' => $this->config['mchNo'],
            'version' => '1.0',
            'reqTime' => strval(round(microtime(true) * 1000)),
            'mchOrderNo' => $mchOrderNo,
            'wayCode' => $params['wayCode'] ?? 'WX_NATIVE',
            'amount' => strval(intval($params['amount'])),
            'currency' => $params['currency'] ?? 'cny',
            'body' => $params['body'] ?? '商品充值',
            'notifyUrl' => $params['notifyUrl'] ?? $this->config['notifyUrl'],
        ];

        // 可选参数
        if (isset($params['clientIp'])) {
            $requestParams['clientIp'] = $params['clientIp'];
        }
        if (isset($params['returnUrl'])) {
            $requestParams['returnUrl'] = $params['returnUrl'];
        }
        if (isset($params['expiredTime'])) {
            $requestParams['expiredTime'] = $params['expiredTime'];
        }
        if (isset($params['channelExtra'])) {
            $requestParams['channelExtra'] = is_array($params['channelExtra']) 
                ? json_encode($params['channelExtra']) 
                : $params['channelExtra'];
        }
        if (isset($params['extParam'])) {
            $requestParams['extParam'] = $params['extParam'];
        }

        // 生成签名
        $requestParams['sign'] = $this->tqPayKit->getSign($requestParams);

        $url = $this->config['baseUrl'] . '/pay/unifiedOrder';
        $response = HttpClient::doPostJson($url, json_encode($requestParams));

        return $this->parseResponse($response, $mchOrderNo);
    }

    /**
     * 微信主扫（NATIVE）
     *
     * @param float $amount 金额（元）
     * @param string $body 商品描述
     * @param string|null $mchOrderNo 商户订单号
     * @return array
     */
    public function wxNative(string $amount, string $body, ?string $mchOrderNo = null): array
    {
        return $this->unifiedOrder([
            'wayCode' => 'WX_NATIVE',
            'amount' => $amount,
            'body' => $body,
            'mchOrderNo' => $mchOrderNo,
        ]);
    }

    /**
     * 微信JSAPI支付
     *
     * @param float $amount 金额（元）
     * @param string $body 商品描述
     * @param string $openid 用户openid
     * @param string|null $mchOrderNo 商户订单号
     * @return array
     */
    public function wxJsapi(string $amount, string $body, string $openid, ?string $mchOrderNo = null): array
    {
        return $this->unifiedOrder([
            'wayCode' => 'WX_JSAPI',
            'amount' => $amount,
            'body' => $body,
            'mchOrderNo' => $mchOrderNo,
            'channelExtra' => [
                'openid' => $openid,
                'subAppid' => $this->config['subAppid'] ?? '',
            ],
        ]);
    }

    /**
     * 支付宝主扫（QR）
     *
     * @param float $amount 金额（元）
     * @param string $body 商品描述
     * @param string|null $mchOrderNo 商户订单号
     * @return array
     */
    public function aliQr(string $amount, string $body, ?string $mchOrderNo = null): array
    {
        return $this->unifiedOrder([
            'wayCode' => 'ALI_QR',
            'amount' => $amount,
            'body' => $body,
            'mchOrderNo' => $mchOrderNo,
        ]);
    }

    /**
     * H5收银台
     *
     * @param float $amount 金额（元）
     * @param string $body 商品描述
     * @param string|null $mchOrderNo 商户订单号
     * @return array
     */
    public function h5Cashier(string $amount, string $body, ?string $mchOrderNo = null): array
    {
        return $this->unifiedOrder([
            'wayCode' => 'H5_CASHIER',
            'amount' => $amount,
            'body' => $body,
            'mchOrderNo' => $mchOrderNo,
        ]);
    }

    /**
     * 验证回调通知签名
     *
     * @param array $params 回调参数
     * @param string|null $signType 签名类型
     * @return bool
     */
    public function verifyNotify(array $params, ?string $signType = null): bool
    {
        $signType = $signType ?? $this->config['signType'];
        $appkey = '';
        if ($signType == 'MD5') {
            $appkey = $this->config['md5Key'];
        } elseif ($signType == 'RSA') {
            $appkey = TqPayKit::formatPublicKey($this->config['pubRsaKey']);
        } else {
            return false;
        }
        return $this->tqPayKit->validSign($params, $appkey, $signType);
    }

    /**
     * 解析响应
     */
    private function parseResponse(string $response, string $mchOrderNo): array
    {
        $result = json_decode($response, true);
        if (empty($result)) {
            return [
                'code' => '9999',
                'msg' => '响应解析失败',
                'mchOrderNo' => $mchOrderNo,
            ];
        }

        if ($result['code'] == '0') {
            $verifyResult = $this->tqPayKit->checkSign($response);
            if (!$verifyResult) {
                return [
                    'code' => '9999',
                    'msg' => '签名验证失败',
                    'mchOrderNo' => $mchOrderNo,
                ];
            }
            return [
                'code' => '0',
                'msg' => 'success',
                'mchOrderNo' => $mchOrderNo,
                'payOrderId' => $result['data']['payOrderId'] ?? '',
                'orderState' => $result['data']['orderState'] ?? '',
                'payDataType' => $result['data']['payDataType'] ?? '',
                'payData' => $result['data']['payData'] ?? '',
            ];
        }

        return [
            'code' => $result['code'] ?? '9999',
            'msg' => $result['msg'] ?? '请求失败',
            'mchOrderNo' => $mchOrderNo,
        ];
    }
}
