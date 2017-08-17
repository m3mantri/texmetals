<?php
namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;


class OrderSender    extends AbstractPlugin
{
    public function beforeSend(\Magento\Sales\Model\Order\Email\Sender\OrderSender $caller,\Magento\Sales\Model\Order $order, $forceSyncMode = false)
    {
        if ( !$order ) return;
        $store = $order->getStore();
        $this->attachEmails($caller,$order,Template::TYPE_ORDER,$store);
    }
}
