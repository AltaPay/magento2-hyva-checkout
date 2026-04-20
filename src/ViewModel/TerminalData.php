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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteIdMaskFactory;

class TerminalData implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $terminalConfig;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    public function __construct(
        ConfigProvider $terminalConfig,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->terminalConfig     = $terminalConfig;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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

    /**
     * Returns Apple Pay configuration data for the given terminal payment method.
     * Returns an empty array when the terminal is not configured as Apple Pay.
     *
     * @param mixed $paymentMethod Payment method object exposing getCode()
     * @return array
     */
    public function getApplePayData($paymentMethod): array
    {
        $code           = $paymentMethod->getCode();
        $paymentMethods = $this->terminalConfig->getActivePaymentMethod();

        if (!isset($paymentMethods[$code]) || empty($paymentMethods[$code]['isapplepay'])) {
            return [];
        }

        $terminal      = $paymentMethods[$code];
        $config        = $this->terminalConfig->getConfig();
        $sdmConfig     = $config['payment'][ConfigProvider::CODE] ?? [];
        $isLoggedIn    = $this->customerSession->isLoggedIn();
        $quote         = $this->checkoutSession->getQuote();

        $maskedQuoteId = '';
        if (!$isLoggedIn) {
            $quoteIdMask   = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
            $maskedQuoteId = (string)$quoteIdMask->getMaskedId();
            if (!$maskedQuoteId) {
                $newMask       = $this->quoteIdMaskFactory->create();
                $newMask->setQuoteId($quote->getId())->save();
                $maskedQuoteId = (string)$newMask->getMaskedId();
            }
        }

        $baseCurrency = !empty($sdmConfig['currencyConfig']);
        $grandTotal   = $baseCurrency
            ? (float)$quote->getBaseGrandTotal()
            : (float)$quote->getGrandTotal();

        $label = !empty($terminal['applepaylabel'])
            ? $terminal['applepaylabel']
            : (!empty($terminal['label']) ? $terminal['label'] : 'Apple Pay');

        // Extract the numeric terminal ID from the payment method code (e.g. terminal1 → 1)
        $terminalNumber = (string)preg_replace('/[^0-9]/', '', $code);

        return [
            'terminalName'   => (string)($terminal['terminalname'] ?? ''),
            'terminalNumber' => $terminalNumber,
            'label'          => (string)$label,
            'countryCode'    => (string)($sdmConfig['countryCode'] ?? ''),
            'currencyCode'   => (string)($sdmConfig['currencyCode'] ?? ''),
            'baseUrl'        => rtrim((string)($sdmConfig['baseUrl'] ?? ''), '/') . '/',
            'grandTotal'     => $grandTotal,
            'isLoggedIn'     => $isLoggedIn,
            'maskedQuoteId'  => $maskedQuoteId,
            'guestEmail'     => !$isLoggedIn ? (string)$quote->getCustomerEmail() : '',
        ];
    }

    /**
     * Returns Apple Pay configuration for the first terminal configured as Apple Pay.
     *
     * @return array
     */
    public function getAllApplePayData(): array
    {
        $paymentMethods = $this->terminalConfig->getActivePaymentMethod();

        foreach ($paymentMethods as $code => $terminal) {
            if (empty($terminal['isapplepay'])) {
                continue;
            }
            $data = $this->getApplePayData(new class($code) {
                private $code;
                public function __construct(string $code) { $this->code = $code; }
                public function getCode(): string { return $this->code; }
            });
            if (!empty($data)) {
                $data['code'] = $code;
                return $data;
            }
        }

        return [];
    }
}