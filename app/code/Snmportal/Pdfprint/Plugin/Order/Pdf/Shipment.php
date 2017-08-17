<?php
namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Shipment as MShipment;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;


class Shipment extends AbstractPlugin
{
    public function aroundGetPdf(
        MShipment $subject,
        \Closure $proceed,
        $documents = []
    ) {
        $engine = $this->getEngine(Template::TYPE_SHIPPING,null);
        if ($engine )
            return $engine->getPdf($documents);
        return $proceed($documents);
    }

}
