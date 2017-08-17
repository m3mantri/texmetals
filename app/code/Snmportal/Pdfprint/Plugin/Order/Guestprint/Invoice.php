<?php
namespace Snmportal\Pdfprint\Plugin\Order\Guestprint;

use Magento\Sales\Model\Order\Pdf\Invoice as MInvoice;
use Magento\Sales\Controller\AbstractController\PrintInvoice;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;
use Snmportal\Pdfprint\Model\Template;

class Invoice extends AbstractFrontendPlugin
{
    public function aroundExecute(
        PrintInvoice $subject,
        \Closure $proceed
    )
    {
        if ( $this->_pdfHelper->isSetFlag('snmportal_pdfprint/invoice/use_pdf_frontend') )
        {
            $invoiceId = (int)$subject->getRequest()->getParam('invoice_id');
            if ($invoiceId) {
                $invoice = $this->_pdfHelper->createObj('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
                $order = $invoice->getOrder();

                if ($this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_INVOICE, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF( $engine, $invoice);
                    }
                }
            }
        }
        return $proceed();
    }
}
