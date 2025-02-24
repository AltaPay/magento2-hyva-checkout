<?php
/**
 * AltaPay Module for Hyva Checkout.
 *
 * Copyright © 2025 AltaPay. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Altapay\HyvaCheckout\Plugin\Hyva_Checkout\Model;

use Hyva\Checkout\Model\MethodMetaData as OriginalMethodMetaData;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use SDM\Altapay\Model\ConfigProvider;

class MethodMetaData
{
    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ConfigProvider
     */
    private $terminalConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LayoutInterface $layout,
        ConfigProvider $terminalConfig
    ) {
        $this->scopeConfig    = $scopeConfig;
        $this->layout         = $layout;
        $this->terminalConfig = $terminalConfig;
    }

    public function aroundRenderIcon(OriginalMethodMetaData $subject, callable $proceed): string
    {
        $method = $subject->getMethod();
        $code   = $method->getCode();

        $paymentMethod = $this->terminalConfig->getActivePaymentMethod();
        $logos         = (isset($paymentMethod[$code]) && !empty($paymentMethod[$code]['terminallogo'])) ? $paymentMethod[$code]['terminallogo'] : [];

        if (preg_match('/terminal\d+/', $code)) {
            return $this->layout->createBlock(Template::class)
                ->setTemplate('Altapay_HyvaCheckout::hyva/logos.phtml')
                ->setLogos($logos)
                ->toHtml();
        }

        return $proceed();
    }

    public function aroundCanRenderIcon(OriginalMethodMetaData $subject, callable $proceed): bool
    {
        $method = $subject->getMethod();
        $code   = $method->getCode();

        if (preg_match('/terminal\d+/', $code)) {
            return true;
        }

        return $proceed();
    }
}