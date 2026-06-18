<?php

namespace AllinPay\Sdk\Extension;

/**
 * 分账扩展类 - 提供分账相关功能
 */
class SettlementExtension extends BaseExtension
{
    /**
     * 获取扩展名称
     */
    public function getName(): string
    {
        return 'settlement';
    }

    /**
     * 批量分账
     *
     * @param string $payOrderId 支付订单号
     * @param array $receivers 分账接收方列表
     * @param string|null $extParam 扩展参数
     * @return array
     */
    public function settlementApply(string $payOrderId, array $receivers, ?string $extParam = null): array
    {
        $params = [
            'payOrderId' => $payOrderId,
            'receivers' => json_encode($receivers),
        ];

        if ($extParam) {
            $params['extParam'] = $extParam;
        }

        return $this->request('/settlement/settlementApply', $params);
    }

    /**
     * 查询分账订单
     *
     * @param string $settlementOrderNo 分账订单号
     * @return array
     */
    public function querySettlementOrder(string $settlementOrderNo): array
    {
        $params = [
            'settlementOrderNo' => $settlementOrderNo,
        ];

        return $this->request('/settlement/querySettlementOrder', $params);
    }

    /**
     * 分账撤销
     *
     * @param string $settlementOrderNo 分账订单号
     * @param string|null $extParam 扩展参数
     * @return array
     */
    public function settlementCancel(string $settlementOrderNo, ?string $extParam = null): array
    {
        $params = [
            'settlementOrderNo' => $settlementOrderNo,
        ];

        if ($extParam) {
            $params['extParam'] = $extParam;
        }

        return $this->request('/settlement/settlementCancel', $params);
    }
}
