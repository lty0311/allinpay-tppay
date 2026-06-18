<?php

namespace AllinPay\Sdk\Extension;

/**
 * 账户扩展类 - 提供余额查询、提现等功能
 */
class AccountExtension extends BaseExtension
{
    /**
     * 获取扩展名称
     */
    public function getName(): string
    {
        return 'account';
    }

    /**
     * 查询余额
     *
     * @return array
     */
    public function queryBalance(): array
    {
        $params = [];

        return $this->request('/account/queryBalance', $params);
    }

    /**
     * 提现申请
     *
     * @param string $amount 提现金额（元）
     * @param string $withdrawOrderNo 提现订单号（商户侧唯一）
     * @param string $accountNo 收款账号
     * @param string $accountName 收款户名
     * @param string $bankCode 银行代码
     * @param string $bankName 银行名称
     * @param string $province 省份
     * @param string $city 城市
     * @param string|null $branchName 开户行名称
     * @param string|null $notifyUrl 提现回调地址
     * @param string|null $extParam 扩展参数
     * @return array
     */
    public function withdrawApply(
        string $amount,
        string $withdrawOrderNo,
        string $accountNo,
        string $accountName,
        string $bankCode,
        string $bankName,
        string $province,
        string $city,
        ?string $branchName = null,
        ?string $notifyUrl = null,
        ?string $extParam = null
    ): array {
        $params = [
            'amount' => strval(intval($amount)),
            'withdrawOrderNo' => $withdrawOrderNo,
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'bankCode' => $bankCode,
            'bankName' => $bankName,
            'province' => $province,
            'city' => $city,
        ];

        if ($branchName) {
            $params['branchName'] = $branchName;
        }
        if ($notifyUrl) {
            $params['notifyUrl'] = $notifyUrl;
        }
        if ($extParam) {
            $params['extParam'] = $extParam;
        }

        return $this->request('/account/withdrawApply', $params);
    }
}
