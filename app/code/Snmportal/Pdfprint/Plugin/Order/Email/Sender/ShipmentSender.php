<?php
namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class ShipmentSender  extends AbstractPlugin
{
    public function beforeSend(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender $caller,\Magento\Sales\Model\Order\Shipment $shipment, $forceSyncMode = false)
    {
        if ( !$shipment ) return;
        $store = $shipment->getOrder()->getStore();
        $this->attachEmails($caller,$shipment,Template::TYPE_SHIPPING,$store);
    }
}
