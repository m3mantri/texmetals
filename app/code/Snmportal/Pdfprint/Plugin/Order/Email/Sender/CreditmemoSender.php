<?php
namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class CreditmemoSender extends AbstractPlugin
{
    public function beforeSend(\Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $caller,\Magento\Sales\Model\Order\Creditmemo $creditmemo, $forceSyncMode = false)
    {
        if ( !$creditmemo ) return;
        $store = $creditmemo->getOrder()->getStore();
        $this->attachEmails($caller,$creditmemo,Template::TYPE_CREDITMEMO,$store);
    }
}
