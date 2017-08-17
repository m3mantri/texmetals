<?php
/**
 * Copyright ? 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\OrderComment\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaypalComment implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $post = $this->request->getPost();
        $quote->setOrderComment(strip_tags($post['order_comment']));
        $this->_objectManager
            ->create('Magento\Quote\Model\QuoteRepository')->save($quote);
        $order->setOrderComment(strip_tags($post['order_comment']));
        $this->_objectManager
            ->create('Magento\Sales\Api\OrderRepositoryInterface')->save($order);
    }

}