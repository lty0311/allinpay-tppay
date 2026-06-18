<?php

namespace AllinPay\Sdk\Extension;

use AllinPay\Sdk\TqPay;
use AllinPay\Sdk\HttpClient;
use AllinPay\Sdk\Util\TqPayKit;

/**
 * 扩展基类 - 提供通用方法供扩展继承
 */
abstract class BaseExtension implements ExtensionInterface
{
    /**
     * @var TqPay
     */
    protected $tqPay;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TqPayKit
     */
    protected $signKit;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $mchNo;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $signType;

    /**
     * @var string
     */
    protected $md5Key;

    /**
     * @var string
     */
    protected $priRsaKey;

    /**
     * @var string
     */
    protected $pubRsaKey;

    /**
     * 初始化扩展
     */
    public function initialize(TqPay $tqPay): void
    {
        $this->tqPay = $tqPay;
        $this->config = $tqPay->getConfig();
        $this->baseUrl = $this->config['baseUrl'];
        $this->mchNo = $this->config['mchNo'];
        $this->appId = $this->config['appId'];
        $this->signType = $this->config['signType'];
        $this->md5Key = $this->config['md5Key'];
        $this->priRsaKey = $this->config['priRsaKey'];
        $this->pubRsaKey = $this->config['pubRsaKey'];
        $this->signKit = new TqPayKit($this->config);
    }

    /**
     * 发送请求
     *
     * @param string $path API路径
     * @param array $params 请求参数
     * @return array
     */
    protected function request(string $path, array $params): array
    {
        $requestParams = $this->buildBaseParams($params);
        $requestParams['sign'] = $this->signKit->getSign($requestParams);

        $url = $this->baseUrl . $path;
        $response = HttpClient::doPostJson($url, json_encode($requestParams));

        return $this->parseResponse($response);
    }

    /**
     * 构建基础参数
     *
     * @param array $params 自定义参数
     * @return array
     */
    protected function buildBaseParams(array $params): array
    {
        $base = [
            'mchNo' => $this->mchNo,
            'appId' => $this->appId,
            'signType' => $this->signType,
            'version' => '1.0',
            'reqTime' => strval(round(microtime(true) * 1000)),
        ];

        return array_merge($base, $params);
    }

    /**
     * 解析响应
     *
     * @param string $response 响应内容
     * @return array
     */
    protected function parseResponse(string $response): array
    {
        $result = json_decode($response, true);
        if (empty($result)) {
            return [
                'code' => '9999',
                'msg' => '响应解析失败',
            ];
        }

        if ($result['code'] == '0') {
            $verifyResult = $this->signKit->checkSign($response);
            if (!$verifyResult) {
                return [
                    'code' => '9999',
                    'msg' => '签名验证失败',
                ];
            }
            return [
                'code' => '0',
                'msg' => 'success',
                'data' => $result['data'] ?? [],
            ];
        }

        return [
            'code' => $result['code'] ?? '9999',
            'msg' => $result['msg'] ?? '请求失败',
        ];
    }

    /**
     * 获取SDK实例
     *
     * @return TqPay
     */
    public function getTqPay(): TqPay
    {
        return $this->tqPay;
    }

    /**
     * 获取配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 获取签名工具
     *
     * @return TqPayKit
     */
    public function getSignKit(): TqPayKit
    {
        return $this->signKit;
    }
}
