<?php
namespace Snmportal\Pdfprint\Plugin\Order\Guestprint;


use Magento\Sales\Controller\AbstractController\PrintAction;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class Order extends AbstractPlugin
{
    public function aroundExecute(
        PrintAction $subject,
        \Closure $proceed
    )
    {
        if ( $this->_pdfHelper->isSetFlag('snmportal_pdfprint/order/use_pdf_frontend') )
        {
            $orderId = (int)$subject->getRequest()->getParam('order_id');
            if ($orderId) {
                $order = $this->_pdfHelper->createObj('Magento\Sales\Api\OrderRepositoryInterface')->get($orderId);
                $engine = $this->getEngine(Template::TYPE_ORDER,$order->getStore());
                if ( $engine )
                {
                    return $this->_pdfHelper->downloadPDF($engine,$order);
                }
            }
        }
        return $proceed();
    }
}
