<?php

namespace AllinPay\Sdk\Extension;

use AllinPay\Sdk\TqPay;

/**
 * 扩展接口 - 所有扩展类必须实现此接口
 */
interface ExtensionInterface
{
    /**
     * 获取扩展名称（唯一标识）
     *
     * @return string
     */
    public function getName(): string;

    /**
     * 初始化扩展
     *
     * @param TqPay $tqPay SDK实例
     * @return void
     */
    public function initialize(TqPay $tqPay): void;
}
