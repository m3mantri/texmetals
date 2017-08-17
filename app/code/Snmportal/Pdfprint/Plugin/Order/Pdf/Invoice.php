<?php
namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice as MInvoice;
use Magento\Sales\Controller\AbstractController\PrintInvoice;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class Invoice extends AbstractPlugin
{
    public function aroundGetPdf(
        MInvoice $subject,
        \Closure $proceed,
        $documents = []
    ) {
        if ( !$this->_pdfHelper->getIsDefaultPrint() )
        {
            $engine = $this->getEngine(Template::TYPE_INVOICE,null);
            if ($engine )
                return $engine->getPdf($documents);
        }
        return $proceed($documents);
    }
}
