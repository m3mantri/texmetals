<?php

namespace MageArray\OrderComment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddViewObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_objectManager = $objectmanager;
    }

    public function execute(EventObserver $observer)
    {
        if ($observer->getElementName() == 'order_shipping_view') {
            $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingViewBlock->getOrder();
            $orderComment = $order['order_comment'];
            $orderCommentBlock = $this->_objectManager->create('Magento\Framework\View\Element\Template');
            $orderCommentBlock->setOrderComment(strip_tags($orderComment));
            $orderCommentBlock->setTemplate('MageArray_OrderComment::order/view/custom.phtml');
            $html = $observer->getTransport()->getOutput() . $orderCommentBlock->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}