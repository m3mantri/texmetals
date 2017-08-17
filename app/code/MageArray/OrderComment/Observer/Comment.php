<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\OrderComment\Observer;

use Magento\Framework\Event\ObserverInterface;

class Comment implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->payment = $paymentMethod;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
        $quote = $quoteRepository->get($order->getQuoteId());
        $order->setOrderComment(strip_tags($quote->getOrderComment()));
    }

}