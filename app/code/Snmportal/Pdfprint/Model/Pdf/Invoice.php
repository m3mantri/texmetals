<?php
namespace Snmportal\Pdfprint\Model\Pdf;

class Invoice extends Base
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems
     */
    protected $helperBlock;

    protected function loadlayout($page, $document, $boutput = true)
    {
        if ($this->registry->registry('current_order'))
            $this->registry->unregister('current_order');
        $this->registry->register('current_order', $document->getOrder());
        if ($this->registry->registry('current_invoice'))
            $this->registry->unregister('current_invoice');
        $this->registry->register('current_invoice', $document);

        $page->addHandle('sales_order_printinvoice');
        $block = $page->getLayout()->getBlock('sales.order.print.invoice');
        /**
         * @var $block \Magento\Sales\Block\Order\PrintOrder\Invoice
         */
        if ($block) {
            $this->helperBlock = $page->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
            $this->buildPaymentInfo($document, $page, $block);
            return $this->parseLayout($block->toHtml());
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('No layout "sales_order_printinvoice" block: "sales.order.print.invoice" found!'));
    }

    protected function getTemplateInfo($document)
    {
        //$this->currentTemplate=null;
        if (!$document)
            return null;
        if ($document->getAuItPrintTemplate()) {
            return $document->getAuItPrintTemplate();
        }
        $template = $this->_templateHelper->getBestTemplateForDocument($document, $this->_useTemplateId);
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
