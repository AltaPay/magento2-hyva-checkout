<?php
/**
 * AltaPay Module for Hyva Checkout.
 *
 * Copyright © 2025 AltaPay. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Altapay\HyvaCheckout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use SDM\Altapay\Model\ConfigProvider;

class TerminalData implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $terminalConfig;

    public function __construct(
        ConfigProvider $terminalConfig
    ) {
        $this->terminalConfig = $terminalConfig;
    }

    public function getTerminalData($param)
    {
        $paymentMethod = $this->terminalConfig->getActivePaymentMethod();
        foreach ($paymentMethod as $key => $method) {
            if ($param->getCode() === $key) {
                return !empty($method['terminalmessage']) ? $method['terminalmessage'] : '';
            }
        }
    }
}