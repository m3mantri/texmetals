<?php
namespace Snmportal\Pdfprint\Model\Pdf;

class Shipment extends Base
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems
     */
    protected $helperBlock;
    protected function loadlayout($page,$document,$boutput=true)
    {
        if ( $this->registry->registry('current_order') )
            $this->registry->unregister('current_order');
        $this->registry->register('current_order', $document->getOrder());
        if ( $this->registry->registry('current_shipment') )
            $this->registry->unregister('current_shipment');
        $this->registry->register('current_shipment', $document);

        $page->addHandle('sales_order_printshipment');
        $block = $page->getLayout()->getBlock('sales.order.print.shipment');
        /**
         * @var $block \Magento\Sales\Block\Order\PrintOrder\Shipment
         */
        if ( $block )
        {
            $this->helperBlock = $page->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
            $this->buildPaymentInfo($document, $page, $block);
            return $this->parseLayout($block->toHtml());
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('No layout "sales_order_printshipment" block: "sales.order.print.shipment" found!'));
    }

    protected function getTemplateInfo($document)
    {
        //$this->currentTemplate=null;
        if ( !$document )
            return null;
        if ( $document->getAuItPrintTemplate())
        {
            return $document->getAuItPrintTemplate();
        }
        $template = $this->_templateHelper->getBestTemplateForDocument($document,$this->_useTemplateId);
        $document->setAuItPrintTemplate($template);
        return $template;
    }


    protected function buildPaymentInfo($document, $page, $block)
    {
        $this->_paymentInfo=array('info'=>'','html'=>'');
        $this->_paymentInfo['html']=$block->getPaymentInfoHtml();
        $this->_paymentInfo['info']=strip_tags($block->getPaymentInfoHtml());
    }

}
