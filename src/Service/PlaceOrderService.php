<?php
/**
 * AltaPay Module for Hyva Checkout.
 *
 * Copyright © 2025 AltaPay. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Altapay\HyvaCheckout\Service;

use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use SDM\Altapay\Model\Gateway;
use SDM\Altapay\Model\SystemConfig;
use Magento\Store\Model\ScopeInterface;

class PlaceOrderService extends AbstractPlaceOrderService
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Gateway
     */
    protected $gateway;

    /**
     * @var SystemConfig
     */
    protected $systemConfig;

    /**
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param UrlInterface $url
     * @param Gateway $gateway
     * @param Session $checkoutSession
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        CartManagementInterface  $cartManagement,
        OrderRepositoryInterface $orderRepository,
        UrlInterface             $url,
        Gateway                  $gateway,
        Session                  $checkoutSession,
        SystemConfig             $systemConfig
    )
    {
        parent::__construct($cartManagement);
        $this->orderRepository = $orderRepository;
        $this->url = $url;
        $this->gateway = $gateway;
        $this->checkoutSession = $checkoutSession;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param EvaluationResultFactory $resultFactory
     * @param int|null $orderId
     * @return EvaluationResultInterface
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory, $orderId = null): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }

    /**
     * @return bool
     */
    public function canPlaceOrder(): bool
    {
        $quote = $this->checkoutSession->getQuote();

        return !$this->isApplePay((string)$quote->getPayment()->getMethod(), $quote->getStore()->getCode());
    }

    /**
     * @return bool
     */
    public function canRedirect(): bool
    {
        $quote = $this->checkoutSession->getQuote();

        return !$this->isApplePay((string)$quote->getPayment()->getMethod(), $quote->getStore()->getCode());
    }

    /**
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     * @throws LocalizedException
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        if (!$orderId) {
            return $this->url->getUrl('checkout/cart');
        }
        $order = $this->orderRepository->get($orderId);
        $methodCode = (string)$order->getPayment()->getMethod();
        $storeCode = $order->getStore()->getCode();

        if ($this->isApplePay($methodCode, $storeCode)) {
            return $this->url->getUrl('checkout/onepage/success');
        }

        $terminalNumber = (int) preg_replace('/[^0-9]/', '', $methodCode);
        $params = $this->gateway->createRequest($terminalNumber, $order->getId());

        if (!isset($params['formurl'])) {
            throw new LocalizedException(__('Redirect URL ("formurl") not found in gateway response.'));
        }

        return $params['formurl'];
    }

    /**
     * Check if the payment method is an AltaPay Apple Pay terminal.
     *
     * @param string $methodCode
     * @param string $storeCode
     * @return bool
     */
    private function isApplePay(string $methodCode, string $storeCode): bool
    {
        $terminalNumber = (int) preg_replace('/[^0-9]/', '', $methodCode);

        if ($terminalNumber === 0) {
            return false;
        }

        return (bool) $this->systemConfig->getTerminalConfig(
            $terminalNumber,
            'isapplepay',
            ScopeInterface::SCOPE_STORES,
            $storeCode
        );
    }
}
