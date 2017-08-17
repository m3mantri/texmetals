<?php
namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class InvoiceSender  extends AbstractPlugin
{
    public function beforeSend(\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $caller,\Magento\Sales\Model\Order\Invoice $invoice, $forceSyncMode = false)
    {
        if ( !$invoice ) return;
        $store = $invoice->getOrder()->getStore();
        $this->attachEmails($caller,$invoice,Template::TYPE_INVOICE,$store);
    }
}
