<?php

namespace AllinPay\Sdk\Util;

use Exception;

/**
 * 通企付签名工具类
 */
class TqPayKit
{
    private $signType;
    private $md5Key;
    private $priRsaKey;
    private $pubRsaKey;
    private $encodingCharset = 'UTF-8';

    public function __construct(array $config)
    {
        $this->signType = $config['signType'] ?? SignType::MD5;
        $this->md5Key = $config['md5Key'] ?? '';
        $this->priRsaKey = self::formatPrivateKey($config['priRsaKey'] ?? '');
        $this->pubRsaKey = self::formatPublicKey($config['pubRsaKey'] ?? '');
    }

    /**
     * 生成签名
     */
    public function getSign(array $params): string
    {
        if ($this->signType == SignType::MD5) {
            return $this->unionSign($params, $this->md5Key, SignType::MD5);
        } elseif ($this->signType == SignType::RSA) {
            return $this->unionSign($params, $this->priRsaKey, SignType::RSA);
        } else {
            throw new Exception('Unsupported sign type: ' . $this->signType);
        }
    }

    /**
     * 验证响应签名
     */
    public function checkSign(string $response): bool
    {
        if (empty($response)) {
            return false;
        }
        $json = json_decode($response, true);
        if ($json['code'] != '0') {
            return false;
        }
        $data = $json['data'];
        $data['sign'] = $json['sign'];
        $signType = $this->signType;
        $appkey = '';
        if ($signType == SignType::MD5) {
            $appkey = $this->md5Key;
        } elseif ($signType == SignType::RSA) {
            $appkey = $this->pubRsaKey;
        } else {
            return false;
        }
        return $this->validSign($data, $appkey, $signType);
    }

    /**
     * 验证签名
     */
    public function validSign(array $params, string $appkey, string $signType): bool
    {
        if (empty($params) || !isset($params['sign'])) {
            return false;
        }
        $sign = $params['sign'];
        $sortedParams = $params;
        unset($sortedParams['sign']);
        ksort($sortedParams, SORT_STRING);
        $sb = '';
        foreach ($sortedParams as $key => $value) {
            if ($value !== null && strlen($value) > 0) {
                $sb .= $key . '=' . $value . '&';
            }
        }
        if ($signType == SignType::MD5) {
            $sb .= 'key=' . $appkey;
        }
        if (substr($sb, -1) == '&') {
            $sb = substr($sb, 0, -1);
        }
        if ($signType == SignType::MD5) {
            $calculatedSign = strtoupper(md5($sb));
            return strtolower($sign) == strtolower($calculatedSign);
        } elseif ($signType == SignType::RSA) {
            return $this->rsaVerify($sb, $sign, $appkey);
        } else {
            return false;
        }
    }

    /**
     * 统一签名
     */
    private function unionSign(array $params, string $appkey, string $signType): string
    {
        $sortedParams = $params;
        if (isset($sortedParams['sign'])) {
            unset($sortedParams['sign']);
        }
        ksort($sortedParams, SORT_STRING);
        $sb = '';
        foreach ($sortedParams as $key => $value) {
            if ($value !== null && strlen($value) > 0) {
                $sb .= $key . '=' . $value . '&';
            }
        }
        if ($signType == SignType::MD5) {
            $sb .= 'key=' . $appkey . '&';
        }
        if (substr($sb, -1) == '&') {
            $sb = substr($sb, 0, -1);
        }
        if ($signType == SignType::MD5) {
            return strtoupper(md5($sb));
        } elseif ($signType == SignType::RSA) {
            return $this->rsaSign($sb, $appkey);
        } else {
            throw new Exception('Sign type error: ' . $signType);
        }
    }

    /**
     * RSA签名
     */
    private function rsaSign(string $content, string $privateKey): string
    {
        $signature = '';
        openssl_sign($content, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }

    /**
     * RSA验签
     */
    private function rsaVerify(string $content, string $sign, string $publicKey): bool
    {
        $signature = base64_decode($sign);
        return openssl_verify($content, $signature, $publicKey, OPENSSL_ALGO_SHA1) === 1;
    }

    /**
     * 格式化私钥
     */
    public static function formatPrivateKey(string $key): string
    {
        if (empty($key)) {
            return '';
        }
        $key = str_replace(['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----', "\n", "\r"], '', $key);
        $formatted = "-----BEGIN PRIVATE KEY-----\n";
        $formatted .= chunk_split($key, 64, "\n");
        $formatted .= "-----END PRIVATE KEY-----";
        return $formatted;
    }

    /**
     * 格式化公钥
     */
    public static function formatPublicKey(string $key): string
    {
        if (empty($key)) {
            return '';
        }
        $key = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $key);
        $formatted = "-----BEGIN PUBLIC KEY-----\n";
        $formatted .= chunk_split($key, 64, "\n");
        $formatted .= "-----END PUBLIC KEY-----";
        return $formatted;
    }
}
