<?php
namespace Snmportal\Pdfprint\Plugin\Order\Frontendprint;

use Magento\Sales\Model\Order\Pdf\Invoice as MInvoice;
use Magento\Sales\Controller\AbstractController\PrintShipment;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;
use Snmportal\Pdfprint\Model\Template;

class Shipment extends AbstractFrontendPlugin
{
    public function aroundExecute(
        PrintShipment $subject,
        \Closure $proceed
    )
    {
        if ( $this->_pdfHelper->isSetFlag('snmportal_pdfprint/creditmemo/use_pdf_frontend') )
        {
            $shipmentId = (int)$subject->getRequest()->getParam('shipment_id');
            if ($shipmentId) {
                $shipment = $this->_pdfHelper->createObj('Magento\Sales\Api\ShipmentRepositoryInterface')->get($shipmentId);
                $order = $shipment->getOrder();
                if ($this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_SHIPPING, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF( $engine, $shipment);
                        return false;
                    }
                }
            }
        }
        return $proceed();
    }
}
