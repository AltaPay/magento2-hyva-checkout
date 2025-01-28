<?php
/**
 * Altapay Module for Magento 2.x.
 *
 * Copyright © 2025 Altapay. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyva\AltapayPayment\Service;

use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\Order;
use SDM\Altapay\Model\Gateway;

class PlaceOrderService extends AbstractPlaceOrderService
{
    private OrderRepositoryInterface $orderRepository;

    private Data $paymentHelper;
    private Manager $messageManager;

    private UrlInterface $url;

    private Session $checkoutSession;
    private FormatExceptionMessages $formatExceptionMessages;
    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;
        /**
     * @var Random
     */
    protected $random;
    /**
     * @var Order
     */
    protected $order;


    /**
     * @var Gateway
     */
    protected $gateway;
    public function __construct(
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        Data $paymentHelper,
        Manager $messageManager,
        UrlInterface $url,
        Random                $random,
        RedirectFactory       $redirectFactory,
        Order                 $order,
        Gateway               $gateway,
        Session $checkoutSession
    ) {
        parent::__construct($cartManagement);
        $this->orderRepository = $orderRepository;
        $this->paymentHelper = $paymentHelper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->random          = $random;
        $this->redirectFactory = $redirectFactory;
        $this->order           = $order;
        $this->gateway         = $gateway;
        $this->checkoutSession = $checkoutSession;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResultInterface
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r("evaluateCompletion",true));

        return $resultFactory->createSuccess();
    }

    public function canPlaceOrder(): bool
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r("canPlaceOrder",true));
        return true;
    }

    public function canRedirect(): bool
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r("canRedirect",true));

        return true;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $order = $this->orderRepository->get($orderId);
        /** @var \Mollie\Payment\Model\Mollie $method */
        $method = $quote->getPayment()->getMethodInstance();

        if ($method instanceof CreditcardVault) {
            $method = $this->paymentHelper->getMethodInstance('terminal3');
        }


        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);


        $params = $this->gateway->createRequest(
            '3',
            $order->getId()
        );

        $logger->info(print_r("params: " . json_encode($params, true), true));

// Extract the custom URL from the params
if (!isset($params['formurl'])) {
    $logger->err("Error: 'formurl' key is missing in params.");
    throw new \Exception("Redirect URL ('formurl') not found in gateway response.");
}

$customUrl = $params['formurl'];
$logger->info(print_r("Redirecting to custom URL: $customUrl", true));

// Use resultRedirect to set the custom URL
$resultRedirect = $this->redirectFactory->create();
$resultRedirect->setUrl($customUrl);

return $customUrl; // Returning the custom URL for redirection

// return $this->url->getUrl('https://testgateway.altapaysecure.com/eCommerce/API/embeddedPaymentWindow?pid=8d700dc3-71b0-4259-9ece-1212dba6505d
// ');
        // try {
        //     return $this->redirectUrl->execute($method, $order);
        // } catch (ApiException $exception) {
        //     $this->messageManager->addErrorMessage($this->formatExceptionMessages->execute($exception));
        //     $this->checkoutSession->restoreQuote();

        //     return $this->url->getUrl('checkout/cart');
        // } catch (LocalizedException $exception) {
        //     $this->checkoutSession->restoreQuote();
        //     throw new LocalizedException(__($this->formatExceptionMessages->execute($exception)));
        // }
    }
}
