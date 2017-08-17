<?php
// @codingStandardsIgnoreFile

namespace Snmportal\Pdfprint\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;

use Snmportal\Pdfprint\Model\Template as SNMTemplate;
use Magento\Framework\App\Filesystem\DirectoryList;


/**
 * Gift Message helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Template extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ObjectManagerInterface $objectManager,
        TaxHelper $taxHelper,
        \Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->taxHelper = $taxHelper;
        parent::__construct(
            $context
        );
    }
    public function createObj($className)
    {
        return $this->_objectManager->create($className);
    }
    public function getIsDefaultPrint()
    {
        return $this->_request->getActionName() == 'print';
    }
    public function getBestTemplateForDocument($document,$defaultId=0)
    {
        $type = null;
        $storeId=0;
        if ( $document instanceof \Magento\Sales\Model\Order\Invoice )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_INVOICE;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order\Shipment )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_SHIPPING;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order\Creditmemo )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_CREDITMEMO;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_ORDER;
            $storeId=$document->getStoreId();
        }
        if ( !is_null($type) )
        {
            $orderTemplates = $this->collectionFactory->create();
            $orderTemplates->addFieldToFilter('type',$type);
            $orderTemplates->addFieldToFilter('is_active',1);
            if ( $storeId ) {
                $orderTemplates->addStoreFilter($storeId);
            }
            $orderTemplates->walk('afterLoad');
            if ( $defaultId )
            {
                foreach ($orderTemplates as $template )
                {
                    if ( $template->getId() == $defaultId )
                        return $template;
                }
            }
            $bestTemplate=null;
            foreach ($orderTemplates as $template )
            {
                if ( !$template->getIsDefault() )
                    continue;
                if ( !$bestTemplate)
                    $bestTemplate = $template;
                if ( $template->getStoreId() && is_array($template->getStoreId()))
                {
                    foreach ( $template->getStoreId() as $sId )
                    {
                        if ( $sId == $storeId )
                        {
                            return $template;
                        }
                    }
                }
            }
            if ( $bestTemplate )
                return $bestTemplate;
            $bestTemplate=null;
            foreach ($orderTemplates as $template )
            {
                if ( !$bestTemplate)
                    $bestTemplate = $template;
                if ( $template->getStoreId() && is_array($template->getStoreId()))
                {
                    foreach ( $template->getStoreId() as $sId )
                    {
                        if ( $sId == $storeId )
                        {
                            return $template;
                        }
                    }
                }
            }
            return $bestTemplate;
        }
        return null;
    }

    public function getProductImage($item)
    {
        $product = $item->getProduct();
        if (!$product) {
            $orderItem = $item->getOrderItem();
            if ($orderItem) {
                $product = $orderItem->getProduct();
            }
        }
        if ($product) {
            if ($product->getImage()) {
                return 'catalog/product/' . $product->getImage();
            }
        }
        return '';
    }

    public function getTemplatesForDocument($document)
    {
        $type = null;
        $storeId=0;
        if ( $document instanceof \Magento\Sales\Model\Order\Invoice )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_INVOICE;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order\Shipment )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_SHIPPING;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order\Creditmemo )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_CREDITMEMO;
            $storeId=$document->getOrder()->getStoreId();
        }
        else if ( $document instanceof \Magento\Sales\Model\Order )
        {
            $type = \Snmportal\Pdfprint\Model\Template::TYPE_ORDER;
            $storeId=$document->getStoreId();
        }
       $orderTemplates = $this->collectionFactory->create();
       $orderTemplates->addFieldToFilter('type',$type);
       $orderTemplates->addFieldToFilter('is_active',1);
       if ( $storeId ) {
            $orderTemplates->addStoreFilter($storeId);
        }
        $orderTemplates->walk('afterLoad');
    //    $order->setAuItPrintTemplates($orderTemplates);
        return $orderTemplates;
    }

    public function getTypinfo($typ)
    {
        switch ( $typ )
        {
            case  SNMTemplate::TYPE_ORDER:
                return array('order','\Snmportal\Pdfprint\Model\Pdf\Order');
                break;
            case  SNMTemplate::TYPE_INVOICE:
                return array('invoice','\Snmportal\Pdfprint\Model\Pdf\Invoice');
                break;
            case  SNMTemplate::TYPE_SHIPPING:
                return array('shipment','\Snmportal\Pdfprint\Model\Pdf\Shipment');
                break;
            case  SNMTemplate::TYPE_CREDITMEMO:
                return array('creditmemo','\Snmportal\Pdfprint\Model\Pdf\Creditmemo');
                break;
        }
        return array('unknown','');
    }
    public function isSetFlag($key,$store=null)
    {
        return $this->scopeConfig->isSetFlag($key,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
    protected function isTypeEnabled($typ,$store)
    {
        $typ = $this->getTypinfo($typ);
        if ( $this->scopeConfig->isSetFlag('snmportal_pdfprint/general/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            &&

            $this->scopeConfig->isSetFlag('snmportal_pdfprint/'.$typ[0].'/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store))
        {
            return true;
        }
        return false;
    }
    protected function isAttachPdfToEmail($typ,$store)
    {
        $typ = $this->getTypinfo($typ);
        return  $this->scopeConfig->isSetFlag('snmportal_pdfprint/'.$typ[0].'/attach_pdf_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

    }

    /**
     * @param $typ
     * @param $store
     * @return \Snmportal\Pdfprint\Model\Pdf\Base |null
     */
    public function getEngine($typ,$store)
    {
        if ( $this->isTypeEnabled($typ,$store) )
        {
            $typ = $this->getTypinfo($typ);
            if ( $typ[1] )
            {
                $engine = $this->_objectManager->create($typ[1]);
                if ($engine) {
                    return $engine;
                }
            }
        }
        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender $caller
     * @return \Magento\Framework\Mail\Message|null
     */
    protected function getMailMessage(\Magento\Sales\Model\Order\Email\Sender $caller)
    {
        $reflectionObject = new \ReflectionObject($caller);
        $reflectionMethod = $reflectionObject->getMethod('getSender');
        $reflectionMethod->setAccessible(true);
        $sender = $reflectionMethod->invoke($caller);

        if ( $sender instanceof \Magento\Sales\Model\Order\Email\SenderBuilder )
        {
            //** @var  $sender \Magento\Sales\Model\Order\Email\SenderBuilder  */
            $reflectionProperty = new \ReflectionProperty($sender, 'transportBuilder');
            $reflectionProperty->setAccessible(true);
            $transportBuilder = $reflectionProperty->getValue($sender);
            /**
             * @var  $transportBuilder \Magento\Framework\Mail\Template\TransportBuilder  */
            if ( $transportBuilder instanceof \Magento\Framework\Mail\Template\TransportBuilder  )
            {
                //** @var $message \Magento\Framework\Mail\Message
                $reflectionProperty = new \ReflectionProperty($transportBuilder, 'message');
                $reflectionProperty->setAccessible(true);
                $message = $reflectionProperty->getValue($transportBuilder);
                if ( $message instanceof \Magento\Framework\Mail\Message )
                {
                    return $message;
                }
            }
        }
        return null;
    }
    protected function attachToEmail(\Magento\Sales\Model\Order\Email\Sender $caller,
                                     $content,
                                     $fileName,
                                     $mimeType='application/pdf',
                                     $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding = \Zend_Mime::ENCODING_BASE64
    )
    {
        $fileName = sprintf('=?utf-8?B?%s?=', base64_encode($fileName));
        $message = $this->getMailMessage($caller);
        if ($message) {
            $message->createAttachment(
                $content,
                $mimeType,
                $disposition,
                $encoding,
                $fileName
            );
        }
    }
    public function attachEmails(\Magento\Sales\Model\Order\Email\Sender $caller,$document,$typ,$store)
    {
        if ( $this->isTypeEnabled($typ,$store) )
        {
            if ( $this->isAttachPdfToEmail($typ,$store) ) {
                $engine = $this->getEngine($typ,$store);
                if ( $engine )
                {
                    $pdf = $engine->getPdf([$document]);
                    if ( $pdf )
                    {
                        $pdfString = $pdf->render();
                        $this->attachToEmail($caller,
                            $pdf->render(),
                            $engine->getEmailFilename());
                    }
                    foreach ( $engine->getEmailAttachments($document) as $attachment )
                    {
                        $fname = $attachment['name']?$attachment['name']:basename($attachment['path']);
                        if ( file_exists($attachment['path']) && is_readable($attachment['path']) )
                        {
                            $this->attachToEmail($caller,
                                file_get_contents($attachment['path']),
                                $fname);
                        }
                    }
                }
            }
        }
    }
//    public function downloadPDF($response,\Snmportal\Pdfprint\Model\Pdf\Base $engine,$documents)
    public function downloadPDF(\Snmportal\Pdfprint\Model\Pdf\Base $engine,$documents)
    {
        if ( !is_array($documents))
            $documents = [$documents];
        $content = $engine->getPdf($documents)->render();
        $fileName = $engine->getEmailFilename();
        return $this->fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );


        /*
        if ( !is_array($documents))
            $documents = [$documents];
        $content = $engine->getPdf($documents)->render();
        $contentLength = null;
        $fileName = $engine->getEmailFilename();

        $contentType = 'application/pdf';

        $response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength === null ? strlen($content) : $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
            ->setHeader('Last-Modified', date('r'), true);

        $response->sendHeaders();
        echo $content;
        flush();
        exit(0);
*/
    }
    protected function getDocumentOrder($document)
    {
        if ( $document instanceof \Magento\Sales\Model\Order )
            return $document;
        if ( $document )
            return $document->getOrder();
        return null;
    }
    public function getTaxTotals($document)
    {
        $_fullInfo = $this->taxHelper->getCalculatedTaxes($document);

        return $_fullInfo;
    }
    public function renderTaxTotals($document,$order,$template)
    {
        $totals = $this->getTaxTotals($document);
        $totalclass = 'tax';

        $html='';
        if ( $template->getData('table_tax_full_summary') ) {
            foreach ($totals as $total) {
                $percent = $total['percent'];
                $percent = sprintf('%s', $percent + 0);
                $percent = $percent ? ' (' . $percent . '%)' : '';
                $amount = $total['tax_amount'];
                $name = __('Tax') . $percent . ':';
                $html .= '<tr class="' . $totalclass . ' part">';
                $html .= '<td class="first"></td>';
                $html .= '<td class="label">' . $template->translateValue($name, 'label') . '</td>';
                $html .= '<td class="amount">' . $template->translateValue($order->formatPrice($amount), 'value') . '</td>';
                $html .= '</tr>';
            }
        }
        if ( $template->getData('table_tax_all') )
        {
            if ( !$template->getData('table_tax_full_summary') || $template->getData('table_tax_all') == 1 || ($template->getData('table_tax_all') == 2 && count($totals) > 1) )
            {
                $amount = $document->getTaxAmount();
                $name = __('Tax'). ':';
                $html .= '<tr class="' . $totalclass . ' total">';
                $html .= '<td class="first"></td>';
                $html .= '<td class="label">' . $template->translateValue($name, 'label') . '</td>';
                $html .= '<td class="amount">' . $template->translateValue($order->formatPrice($amount), 'value') . '</td>';
                $html .= '</tr>';
            }
        }
        return $html;
    }
}
