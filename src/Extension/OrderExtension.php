<?php

namespace AllinPay\Sdk\Extension;

/**
 * 订单扩展类 - 提供订单查询、关闭等功能
 */
class OrderExtension extends BaseExtension
{
    /**
     * 获取扩展名称
     */
    public function getName(): string
    {
        return 'order';
    }

    /**
     * 查询支付订单
     *
     * @param string $payOrderId 通企付订单号（与mchOrderNo二选一）
     * @param string|null $mchOrderNo 商户订单号（与payOrderId二选一）
     * @return array
     */
    public function queryPayOrder(string $payOrderId, ?string $mchOrderNo = null): array
    {
        $params = [
            'payOrderId' => $payOrderId,
        ];

        if ($mchOrderNo) {
            $params['mchOrderNo'] = $mchOrderNo;
        }

        return $this->request('/pay/queryPayOrder', $params);
    }

    /**
     * 关闭支付订单
     *
     * @param string $payOrderId 通企付订单号
     * @param string|null $mchOrderNo 商户订单号
     * @return array
     */
    public function closePayOrder(string $payOrderId, ?string $mchOrderNo = null): array
    {
        $params = [
            'payOrderId' => $payOrderId,
        ];

        if ($mchOrderNo) {
            $params['mchOrderNo'] = $mchOrderNo;
        }

        return $this->request('/pay/closePayOrder', $params);
    }

    /**
     * 统一退款
     *
     * @param string $payOrderId 通企付订单号
     * @param string $amount 退款金额（元）
     * @param string $refundOrderNo 退款订单号（商户侧唯一）
     * @param string|null $mchOrderNo 商户订单号
     * @param string|null $refundReason 退款原因
     * @param string|null $notifyUrl 退款回调地址
     * @return array
     */
    public function refundOrder(
        string $payOrderId,
        string $amount,
        string $refundOrderNo,
        ?string $mchOrderNo = null,
        ?string $refundReason = null,
        ?string $notifyUrl = null
    ): array {
        $params = [
            'payOrderId' => $payOrderId,
            'amount' => strval(intval($amount)),
            'refundOrderNo' => $refundOrderNo,
        ];

        if ($mchOrderNo) {
            $params['mchOrderNo'] = $mchOrderNo;
        }
        if ($refundReason) {
            $params['refundReason'] = $refundReason;
        }
        if ($notifyUrl) {
            $params['notifyUrl'] = $notifyUrl;
        }

        return $this->request('/pay/refundOrder', $params);
    }

    /**
     * 查询退款订单
     *
     * @param string $refundOrderNo 退款订单号
     * @return array
     */
    public function queryRefundOrder(string $refundOrderNo): array
    {
        $params = [
            'refundOrderNo' => $refundOrderNo,
        ];

        return $this->request('/pay/queryRefundOrder', $params);
    }
}
