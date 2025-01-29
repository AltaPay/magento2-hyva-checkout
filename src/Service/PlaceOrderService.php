<?php
/**
 * AltaPay Module for Hyva Checkout.
 *
 * Copyright © 2025 AltaPay. All rights reserved.
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
        CartManagementInterface  $cartManagement,
        OrderRepositoryInterface $orderRepository,
        Data                     $paymentHelper,
        Manager                  $messageManager,
        UrlInterface             $url,
        Random                   $random,
        RedirectFactory          $redirectFactory,
        Order                    $order,
        Gateway                  $gateway,
        Session                  $checkoutSession
    )
    {
        parent::__construct($cartManagement);
        $this->orderRepository = $orderRepository;
        $this->paymentHelper = $paymentHelper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->random = $random;
        $this->redirectFactory = $redirectFactory;
        $this->order = $order;
        $this->gateway = $gateway;
        $this->checkoutSession = $checkoutSession;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }

    public function canPlaceOrder(): bool
    {
        return true;
    }

    public function canRedirect(): bool
    {
        return true;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $order = $this->orderRepository->get($orderId);

        $params = $this->gateway->createRequest(
            '1',
            $order->getId()
        );

        // Extract the custom URL from the params
        if (!isset($params['formurl'])) {
            throw new \Exception("Redirect URL ('formurl') not found in gateway response.");
        }

        $customUrl = $params['formurl'];

        // Use resultRedirect to set the custom URL
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setUrl($customUrl);

        return $customUrl; // Returning the custom URL for redirection
    }
}
