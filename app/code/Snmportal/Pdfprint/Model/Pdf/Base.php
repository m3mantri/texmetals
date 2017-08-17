<?php

namespace Snmportal\Pdfprint\Model\Pdf;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;

Autoloader::register();


abstract class Base extends AbstractPdf
{
    CONST ALIAS_NUM_PAGE = '{:pnp:}';
    CONST ALIAS_TOT_PAGES = '{:ptp:}';

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems
     */
    protected $helperBlock;

    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\Renderer
     */
    protected $_pdfRenderer;
    protected $HTMLDocuemnts=array();
    protected $_page_nr;
    protected $reflowDocumentId;
    protected $docPageCount=0;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    protected $document;

    protected $_paymentInfo;

    /**
     * @var \Snmportal\Pdfprint\Logger\Logger
     */
    protected $_snmLogger;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
//    protected $_layoutFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    //protected $resultLayoutFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;



    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\ResultFactory
     */
    protected $resultFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    //protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\FilterFactory
     */
    protected $filterFactory;
    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\Filter\HelperFactory
     */
    protected $helperFactory;
    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\Filter
     */
    protected $_processor;
    /**
     * @var \Magento\GiftMessage\Helper\MessageFactory
     */
    protected $messageFactory;
    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
//    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;
    protected $_documents;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDirectory;

    protected $pageBackgrounds=array();

    /**
     * Core registry
     *
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_templateHelper;

    /**
     * Checkout helper
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    protected $_useTemplateId;
    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
//        \Magento\Framework\App\ViewInterface $view,
        //\Magento\Framework\View\LayoutFactory $layoutFactory,
        //\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Snmportal\Pdfprint\Model\Pdf\ResultFactory $resultFactory,
        \Snmportal\Pdfprint\Logger\Logger $snmLogger,
        //\Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\MessageFactory $messageFactory,
        \Snmportal\Pdfprint\Model\Pdf\FilterFactory $filterFactory,
        \Snmportal\Pdfprint\Model\Pdf\Filter\HelperFactory $helperFactory,
        //\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\DesignInterface $design,
        \Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        \Snmportal\Pdfprint\Helper\Template $templateHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    ) {
        $this->_templateHelper = $templateHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_snmLogger = $snmLogger;
        $this->registry=$registry;
        $this->resultFactory = $resultFactory;
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_countryFactory = $countryFactory;
        $this->_appEmulation = $appEmulation;
        //$this->_layoutFactory = $layoutFactory;
        //$this->resultLayoutFactory = $resultLayoutFactory;
        //$this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->messageFactory = $messageFactory;
        //$this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->design = $design;
        $this->appState = $appState;
        $this->filterFactory = $filterFactory;
        $this->helperFactory = $helperFactory;
        $this->systemTmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
    }
    abstract protected function loadlayout($page,$document,$boutput=true);


    //abstract protected function parseLayout($html);

    /**
     * @param $document
     * @return \Snmportal\Pdfprint\Model\Template
     */
    abstract protected function getTemplateInfo($document);


    public function getEmailAttachments($document)
    {
        $templateInfo = $this->getTemplateInfo($document);
        if ( $templateInfo )
            return $templateInfo->getEmailAttachments();
        return array();
    }
    public function getEmailFilename()
    {
        $fileName = '';
        if ( $this->HTMLDocuemnts && count($this->HTMLDocuemnts) )
        {
            foreach ($this->HTMLDocuemnts as $doc )
            {
                if ( $fileName ) $fileName .='-';
                $fileName .= $doc['pdfname'];
            }

        }

        if ( !$fileName )
            $fileName = 'unknown';
        return $fileName.'.pdf';
    }
    protected function getFreeItems($page,$document)
    {
        $result=array();
        if ( $this->getTemplateInfo($document) )
            $result = $this->getTemplateInfo($document)->getFreePrintItems($page);
        return $result;
    }
    public function setRenderTemplateId($templateId)
    {
        $this->_useTemplateId = $templateId;
        return $this;
    }
    protected function logMessage($msg)
    {
        $this->_snmLogger->info($msg);
    }
    protected function getStoreConfig($pfad,$store=null)
    {
        return  $this->_scopeConfig->getValue(
            $pfad,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    protected function _drawItem(\Magento\Framework\DataObject $item, \Zend_Pdf_Page $page, \Magento\Sales\Model\Order $order)
    {
        return $page;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getCurrentOrder()
    {
        return $this->getDocumentOrder($this->document);
    }

    /**
     * @param $document
     * @return \Magento\Sales\Model\Order
     */
    protected function getDocumentOrder($document)
    {
        if ( $document instanceof \Magento\Sales\Model\Order )
            return $document;
        if ( $document )
            return $document->getOrder();
        return null;
    }
    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        return $page;
    }
    protected function buildlayout($document,$boutput=true)
    {
   //     $this->design->setDesignTheme('Magento/blank', 'frontend');

        $page = $this->resultFactory->create();
        $block = null;
        if ( $this->registry->registry('current_order') )
            $this->registry->unregister('current_order');
        $result = $this->loadlayout($page,$document,$boutput);
        return $result ;
    }
    public function _endPage($info)
    {
    }
    public function _beginPageReflow($info)
    {
        $canvas = $info['canvas'];
        $frame = $info['frame'];
        switch ( $frame->get_node()->tagName )
        {
            case 'body':
                $docId = $frame->get_node()->getAttribute('data-id');
                if ( $docId != $this->reflowDocumentId )
                {
                    $this->reflowDocumentId = $docId;
                    $this->_page_nr=0;
                    $this->document = $this->getPrintDocument($docId);
                    $this->_processor=null;
                    if ( !$this->getTemplateInfo($this->document) )
                        throw new \Magento\Framework\Exception\LocalizedException(__('No template found!'));
                }
                break;
            default:
                break;
        }
        $pn = $this->_page_nr+1;
        if ( $template = $this->getTemplateInfo($this->document) )
        {
            $x = $this->getPDFRenderer()->mm2pt($template->getMargin($pn,'left'));
            $y = $this->getPDFRenderer()->mm2pt($template->getMargin($pn,'top'));
            $w = $this->getPDFRenderer()->mm2pt($template->getMargin($pn,'right'));
            $h = $this->getPDFRenderer()->mm2pt($template->getMargin($pn,'bottom'));
            $w = $canvas->get_width() - $w - $x;
            $h = $canvas->get_height() - $h ;
            if ( $frame->get_root() )
            {
                $frame->set_containing_block($x, $y, $w, null);
                $frame->get_root()->set_containing_block($x, $y, $w, $h);
            }

        }
    }
    public function _beginPage($info)
    {
        $canvas = $info['canvas'];
        $this->_page_nr++;
        $pageNumber = $this->_page_nr;
        $allpageNumber = $canvas->get_page_number();
        $this->pageBackgrounds[$allpageNumber]=array();
        if (  $this->getPdfTemplate($this->document) )
        {
            $this->pageBackgrounds[$allpageNumber]=array('source'=>$this->getPdfTemplate($this->document),'page'=>$pageNumber==1?1:2);
        }
        $this->insertFreeItems($info,$this->document );
    }
    protected function insertFreeItems($info,$document)
    {
        $canvas = $info['canvas'];
        $docPageNumber = $canvas->get_page_number();
        $docPageCount = ( $this->docPageCount)?$this->docPageCount: $canvas->get_page_number();

        $pageNumber = $this->_page_nr;


        $items = $this->getFreeItems($pageNumber <= 1 ? 1 : 2,$document);
        $docCSS =  '';
        if ( $this->getTemplateInfo($document) )
            $docCSS = $this->getTemplateInfo($document)->getCss();
        if ( $items && is_array($items))
            foreach ( $items as $item )
            {
                $blockInfo  = $this->getStyleItem($item);
                if ( !$blockInfo ) continue;
                $v = $blockInfo['value'];
                if ( strpos($v,'{{') !== false )
                {
                    $v = $this->getProcessor()->filter($v);
                }
                $v = str_replace(array(self::ALIAS_NUM_PAGE,self::ALIAS_TOT_PAGES),array($docPageNumber,$docPageCount), $v);
                //$v = $this->checkArabic($v);
               // $v = $this->getPDFRenderer()->checkRTL($v);
                $this->drawBlock($v,$blockInfo,$docCSS);
            }
    }
    public function drawBlock($txt,$blockInfo,$docCSS)
    {
        $w = $blockInfo['width'];
        $h = $blockInfo['height'];
        $x = $blockInfo['x_pos'];
        $y = $blockInfo['y_pos'];



        $style = "position:absolute;left:{$x}mm;top:{$y}mm;width:{$w}mm;height:{$h}mm;";
        $html = '<div class="default" style="margin:0;padding:0;'.$style.'">';
        $html .= '<div style="margin:0;padding:0;" class="'.$blockInfo['style'].'">'.$txt.'</div></div>';
        $this->logMessage("DRAW FREE ITEM\n".$html);
        $this->getPDFRenderer()->writeHTML($html,$docCSS);
    }

    protected function getPDFRenderer()
    {
        if ( !$this->_pdfRenderer )
        {
            //$this->logMessage("+++getPDFRenderer");
            $bgTemplate = $this->getPdfTemplate($this->document);
            $options=array();
            $options['defaultPaperSize']='a4';

            //$options['enable_php']=true;
            $options['enable_remote']=true;
            $options['enable_font_subsetting']=true;

            if (  $bgTemplate )
            {
                $fpdf = new \Snmportal\External\fpdi\FPDI_Protection('P', 'pt');
                $fpdf->setSourceFile($bgTemplate);
                $tplidx = $fpdf->importPage(1);
                if ( $tplidx )
                {
                    $size = $fpdf->getTemplateSize($tplidx, 0, 0);
                    $options['defaultPaperSize']=array(0, 0, $size['w'], $size['h']);
                }
            }
            $this->_pdfRenderer = new Renderer($options);//$this);
            $this->_pdfRenderer->setCallbacks(array(

                array('event'=>'begin_page_reflow','f'=>array($this,'_beginPageReflow')),
                //array('event'=>'end_page_render','f'=>array($this,'_beginPage')),
                array('event'=>'begin_page_render','f'=>array($this,'_beginPage')),
            ));
        }

        return $this->_pdfRenderer;
    }
    protected function getPrintDocument($docId)
    {
        foreach ($this->_documents as $document) {
            if ( $document->getId() ==  $docId )
                return $document;
        }
        return null;
    }
    public function getPdf($documents = array())
    {
        $this->_beforeGetPdf();
        try {
            $this->_documents=$documents;
            foreach ($documents as $document) {
                $this->_processor=null;
                $this->paymenInfoBlock=null;
                $this->document = $document;
                $this->logMessage(__("BUILD Info for document(%2):%1\n",$document->getId(),get_class($document)) );
                $this->_appEmulation->startEnvironmentEmulation($document->getStoreId(),\Magento\Framework\App\Area::AREA_FRONTEND,true);
                $this->appState->emulateAreaCode('frontend', [$this, 'renderDocument']);
                $this->_appEmulation->stopEnvironmentEmulation();
                $this->logMessage(__("BUILD End for document(%2):%1\n",$document->getId(),get_class($document)) );
            }

            $allHTML='<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html>';
            foreach ( $this->HTMLDocuemnts as $htmlInfo )
            {
                $allHTML .= $htmlInfo['html'];
            }
            $allHTML .= '</html>';

            // STep 1

            $this->getPDFRenderer()->loadHtml($allHTML, 'UTF-8');
            $this->getPDFRenderer()->render();
            $this->logMessage("PAGE HTML\n".$allHTML);
            // STep 2
            $this->docPageCount = $this->getPDFRenderer()->getCanvas()->get_page_count();
            $stringSubsetsText = $this->_pdfRenderer->getCanvas()->get_cpdf()->stringSubsetsText;
            if ( 1 ){
                $this->_pdfRenderer=null;
                $this->pageBackgrounds=array();
                $this->reflowDocumentId=0;
                $this->getPDFRenderer()->loadHtml($allHTML, 'UTF-8');
                $this->getPDFRenderer()->stringSubsetsText = $stringSubsetsText;
                $this->getPDFRenderer()->render();
            }

            $contentPdfFilename = $this->getTmpFileName();
            $this->systemTmpDirectory->writeFile($contentPdfFilename, $this->getPDFRenderer()->getStream());
            $contentPdf = $this->systemTmpDirectory->getAbsolutePath($contentPdfFilename);

           // file_put_contents('test.pdf',file_get_contents($contentPdf));

            $fpdf = new \Snmportal\External\fpdi\FPDI_Protection();
            $pageCount = $fpdf->setSourceFile($contentPdf);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $fpdf->setSourceFile($contentPdf);
                $templateId = $fpdf->importPage($pageNo);
                $size = $fpdf->getTemplateSize($templateId);
                // create a page (landscape or portrait depending on the imported page size)
                if ($size['w'] > $size['h']) {
                    $fpdf->AddPage('L', array($size['w'], $size['h']));
                } else {
                    $fpdf->AddPage('P', array($size['w'], $size['h']));
                }
                if ( isset($this->pageBackgrounds[$pageNo]['source']) &&  $this->pageBackgrounds[$pageNo]['source'] )
                {
                    $fpdf->setSourceFile($this->pageBackgrounds[$pageNo]['source']);
                    $templateIdBg = $fpdf->importPage($this->pageBackgrounds[$pageNo]['page']);
                    $fpdf->useTemplate($templateIdBg);
                }
                // use the imported page
                $fpdf->useTemplate($templateId);
            }

            if ( count($this->HTMLDocuemnts) == 1)
            {
                /**
                 * Add Appendix...
                 */
                foreach ( $this->HTMLDocuemnts as $htmlInfo )
                {
                    $appendix = $htmlInfo['appendix'];
                    if ( $appendix )
                    {
                        $pageCount = $fpdf->setSourceFile($appendix);
                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                            $templateId = $fpdf->importPage($pageNo);
                            $size = $fpdf->getTemplateSize($templateId);
                            // create a page (landscape or portrait depending on the imported page size)
                            if ($size['w'] > $size['h']) {
                                $fpdf->AddPage('L', array($size['w'], $size['h']));
                            } else {
                                $fpdf->AddPage('P', array($size['w'], $size['h']));
                            }
                            // use the imported page
                            $fpdf->useTemplate($templateId);
                        }
                    }
                    break;
                }

            }

        }catch (\Exception $e){

            $pdf = new \Zend_Pdf();
            $this->_setPdf($pdf);
            $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
            $this->_setFontBold($page, 8);
            $this->_getPdf()->pages[] = $page;

             if ( $this->document )
                $page->drawText(__('Document-ID:%1, Increment-id: %2',$this->document->getId(),$this->document->getIncrementId()), 15, 820, 'UTF-8');
            foreach ( str_split(__('Exception:') .$e->getMessage(),140) as $idx => $v )
            {
                $page->drawText($v, 15, 800-($idx*9), 'UTF-8');
            }
            return $this->_getPdf();
        }
        $this->_afterGetPdf();


        $pdf = new Zpdf();
        $this->_setPdf($pdf);
        //file_put_contents('test2.pdf',$fpdf->Output('S'));
        $pdf->setTCPFStream($fpdf->Output('S'));

        $this->_pdfRenderer=null;
        $this->systemTmpDirectory->delete($contentPdfFilename);
        return $this->_getPdf();
    }
    protected function getTmpFileName()
    {
        return uniqid('snmpdf', true);
    }
    public function newPage(array $settings = array())
    {
    }
    public function drawLines($lines,$blockName)
    {
    }
    public function renderDocument()
    {
        $document = $this->document;
        $order = $this->getDocumentOrder($document);
        $this->_localeResolver->emulate($order->getStore()->getId());

//        $order->getStore()->getDefaultCurrency()->get
  //      $this->_localeResolver->setLocale()
        // Bug Fix reset Locale
//        $reflectionProperty = new \ReflectionProperty($order, '_orderCurrency');
  //      $reflectionProperty->setAccessible(true);
    //    $reflectionProperty->setValue($order,null);
      //  $currency = $order->getOrderCurrency();

        $template = $this->getTemplateInfo($document);
        if ( !$template )
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('No template found!'));
        }

        $layoutInfo = $this->buildlayout($document);

        $html = '';
        $html .= '<style>@page {  margin: 0cm;} .product-img{height:10mm;}'.$template->getCss().'</style><body class="default" data-id="'.$document->getId().'">';
        if ( $tmp = $this->getBuildBeforeTableText($document) )
        {
            $html .= '<div class="before-table-text">'.$tmp.'</div>';
        }


        if ( $tmp = $this->renderDataTable($document,$layoutInfo) )
        {
            $html .= '<div class="data-table">';
            $html .= $tmp;
            $html .= $this->renderTotalTable($document,$layoutInfo);
            $html .= '</div>';
        }
        if ( $tmp = $this->getBuildAfterTableText($document) )
        {
            $html .= '<div class="after-table-text">'.$tmp.'</div>';
        }
        $html .= '</body>';
        $this->HTMLDocuemnts[]=array(
            'html'=>$html,
            'pdfname'=>$this->getProcessor()->filter($template->getAttachFilename()),
            'appendix'=>$template->getAppendixPath()
        );
        return ;

    }

    protected function getStyleItem($items)
    {
        $blockInfo=array('x_pos'=>10,'y_pos'=>10,'width'=>60,'height'=>40,'style'=>'default','script'=>'markup','value'=>'');
        $items = $this->setArrayDefault($items,$blockInfo);
        return $items;//Mage::getModel('auit_pdf/pdf_style',$items);
    }
    protected function setArrayDefault($x,$defaults)
    {
        foreach ( $defaults as $key => $v)
        {
            if ( !isset($x[$key])|| trim($x[$key]) === '' )
            {
                $x[$key]=$v;
            }
        }
        return $x;
    }

    protected function getProcessor()
    {
        if ( !$this->_processor )
        {
            //$this->logMessage("+++getProcessor");
            $order = $this->getCurrentOrder();
            $processor = $this->filterFactory->create();


            /**
            * $tracksCollection = $order->getTracksCollection();
            * $trackingInfo = '';
            * foreach($tracksCollection->getItems() as $track) {
             * error_log("\n" . print_r($track->debug(), true), 3, 'auit.log');
             * if ( empty($trackingInfo))
                    * $trackingInfo='<table class="trackinginfo" cellpadding="0" cellspacing="0">';
                * $trackingInfo .= '<tr><td class="title">'.$track->getData('title').'</td>';
             * $trackingInfo .= '<td class="number">'.$track->getData('track_number').'</td>';
                * $trackingInfo .= '</tr>';
             * }
             *
             *
            * if ( !empty($trackingInfo))
                * $trackingInfo.='</table>';
 *
* $_giftMessage = null;
            * if ( $order->getGiftMessageId() )
            * {
                * $giftMsg = $this->messageFactory->create();
                * $_giftMessage = $giftMsg->getGiftMessage($order->getGiftMessageId() );
             *
             * }
             * */
            /**
             * @var \Snmportal\Pdfprint\Model\Pdf\Filter\Helper $filterhelper
             */
            $filterhelper =  $this->helperFactory->create();
            $filterhelper->setProcessor($processor)->setOrder($order);


            /***
             *
             */

            // \Magento\Sales\Model\Order\Address

            $data = array(	'order'=>$order,
                'payment_info'=>$this->_paymentInfo?$this->_paymentInfo['info']:'',
                'payment_html'=>$this->_paymentInfo?$this->_paymentInfo['html']:'',
                'customer'=>$this->getOrderCustomer($order),
                'helper'=> $filterhelper,
                'addressRenderer'=>$this->addressRenderer,
                'billingaddress'=>$this->getBillingAddress($this->document),
                'shippingaddress'=>$this->getShippingAddress($this->document),
//                'tracking_info'=>$trackingInfo,
                'payment_method'=>$order->getPayment()->getMethod(),
                'order'=>$order,
                'templateinfo'=>$this->getTemplateInfo($this->document),
                'entity'=>$this->document,
//                'giftmessage'=>$_giftMessage,
//                'page_current'=>$this->_tcpdf->getRSCPage().$this->_tcpdf->getAliasNumPage(),
                'page_current'=>self::ALIAS_NUM_PAGE,
                'page_count'=>self::ALIAS_TOT_PAGES,
                'order_date' => $filterhelper->formatDate($order->getStore(),$order->getCreatedAt()),
            //    'invoice_date' => Mage::helper('core')->formatDate($invoice->getCreatedAtDate(), 'medium', false),
                'entity_date' => $filterhelper->formatDate($order->getStore(),$this->document->getCreatedAt())
            );
            if ( $this->document instanceof \Magento\Sales\Model\Order\Shipment )
            {
                $data['shipment'] =$this->document;
                $data['shipment_date'] = $filterhelper->formatDate($order->getStore(),$this->document->getCreatedAt());
            }
            if ( $this->document instanceof \Magento\Sales\Model\Order\Creditmemo )
            {
                $data['creditmemo'] =$this->document;
                $data['creditmemo_date'] = $filterhelper->formatDate($order->getStore(),$this->document->getCreatedAt());
            }
            $processor->setVariables($data);
            $this->_processor=$processor;
        }
        return $this->_processor;
    }
    protected function getOrderCustomer(\Magento\Sales\Model\Order $order)
    {
        if ( $order->getCustomer() )
            return $order->getCustomer();
        $customer = $this->customerFactory->create()->load($order->getCustomerId());
        $order->setCustomer($customer);
        return $customer;
    }
    protected function preFormatAddress($address)
    {
        $address->explodeStreetAddress();
        if ( $address->getCountryId() && !$address->getCountry() )
            $address->setCountry($this->_countryFactory->create()->loadByCode($address->getCountryId())->getName());
        return $address;
    }
    protected function getBillingAddress($order)
    {
        if ( $order->getBillingAddress() )
        {
            return $this->preFormatAddress($order->getBillingAddress());
        }
        return null;
    }

    /**
     * @param $order
     * @return \Magento\Sales\Model\Order\Address
     */
    protected function getShippingAddress($order)
    {
        if ( $order->getShippingAddress() )
        {
            return $this->preFormatAddress($order->getShippingAddress());
        }
        return null;
    }
    protected function getBuildAfterTableText($document)
    {
        if ( $this->getTemplateInfo($document))
        {
            $v = $this->getTemplateInfo($document)->getData('text_after_table');
            return  $this->getProcessor()->filter($v);
        }
        return '';
    }
    protected function getBuildBeforeTableText($document)
    {
        if ( $this->getTemplateInfo($document)) {
            $v = $this->getTemplateInfo($document)->getData('text_before_table');
            return $this->getProcessor()->filter($v);
        }
        return '';
    }


    protected function trimHTML($html)
    {
        return trim($html);
    }
    protected function trimNodeValue($node)
    {
        $v = str_replace("\t","",$node->nodeValue);
        return trim($v);
    }
    protected function DOMinnerHTML( $element,$checkArabic=false)
    {
        $innerHTML = '';
        $children  = $element->childNodes;
        foreach ($children as $child)
        {

            if ( $child->nodeType == XML_TEXT_NODE )
            {
                if ( $checkArabic )
                {

                    if ( $tmp = trim($child->textContent) )
                    {
                        $Arabic = new \Snmportal\External\Arabic\I18N_Arabic_Glyphs('Glyphs');
                        $innerHTML .= $Arabic->utf8Glyphs($tmp);
                    }

                }else
                    $innerHTML .= trim($child->textContent);
            }

            else {
                if ( !$child->hasChildNodes() )
                    $innerHTML .= '<'.$child->nodeName.'/>';
                else {
                    $innerHTML .= '<'.$child->nodeName;
                    foreach ( $child->attributes as $name => $attrNode )
                    {
                        $innerHTML .= " $name=\"".$attrNode->nodeValue.'"';
                    }
                    $innerHTML .= '>';
                    /*
                    if ( $child->getAttribute('data-label') )
                    {
                        $innerHTML .= '<span class="data-label">'.$child->getAttribute('data-label').'</span>';
                    }
                    */

                    $innerHTML .= $this->DOMinnerHTML($child,$checkArabic);

                    $innerHTML .= '</'.$child->nodeName.'>';
                }
            }

            //$innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    protected function getColectionItem($document,$id)
    {

        //** @var $document \Magento\Sales\Model\Order
        return $document->getItemsCollection()->getItemById($id);


    }
    protected function renderDataTable($document,$layoutInfo)
    {
        $template = $this->getTemplateInfo($document);
        $cols = $this->getDataTableCols($document);
        $mainclass='table-items custom';
        if ( !$cols )
        {
            $mainclass='table-items default';
            $cols = $layoutInfo['cols'];
        }
        $html='<div class="table-items-frame"><table class="'.$mainclass.'" cellpadding="0" cellspacing="0">';
        $html.='<thead><tr>';
        foreach ( $cols as $col ) {
            if( isset($col['class']) )
            {
                $html.='<th class="'.$col['class'].'">';
                $html.=$template->translateValue($col['valueHTML'],'label');
                $html.='</th>';
            }else {
                $type = isset($col['type'])?$col['type']:'';
                $hcss = isset($col['hcss'])?$col['hcss']:'';
                $html.='<th class="'.$type.' '.$hcss.'"';
                if ( isset($col['width']) && trim($col['width']))
                {
                    $html.=   ' style="width:'.$col['width'].'" ';
                }
                $html.=    '>';
                $html.=$template->translateValue($col['name'],'label');
                $html.='</th>';
            }
        }
        $html.='</tr></thead>';
        $html.='<tbody>';


        $idx=0;
        foreach ( $layoutInfo['rows'] as $row ) {
            if (!$row['special']) {
                $idx++;
                $html .= '<tr class="' . $row['class'] . '">';
                $row['idx'] = $idx;
                $item = $this->getColectionItem($document, $row['itemid']);
                if ($this->helperBlock)
                    $this->helperBlock->setPriceDataObject($item);
                foreach ($cols as $col) {
                    if (isset($col['class'])) {
                        $html .= '<td class="' . $col['class'] . '">';
                        $html .= $template->translateValue($this->colDataTableValue($document, $row, $col), 'value');
                        $html .= '</td>';
                    }
                    else  {
                        $type = isset($col['type'])?$col['type']:'';
                        $css = isset($col['css'])?$col['css']:'';
                        $html .= '<td class="' . $type . ' ' . $css . '">';
                        $html .= $template->translateValue($this->getDataTableValue($item, $document, $row, $col), 'value');
                        $html .= '</td>';
                    }
                }
                $html .= '</tr>';
            }else {
                if ( isset($row['cols']) && is_array($row['cols']) && count($row['cols']))
                {
                    $html .= '<tr class="' . $row['class'] . '">';
                    $html .= '<td class="special" colspan="'.count($cols).'">';
                    $row['idx'] = $idx;
                    $item = $this->getColectionItem($document, $row['itemid']);
                    if ($this->helperBlock)
                        $this->helperBlock->setPriceDataObject($item);
                    foreach ($row['cols'] as $col) {
                        $html .= $template->translateValue($this->colDataTableValue($document, $row, $col), 'value');
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                }
            }
        }

        $html.='</tbody></table></div>';
       // error_log("\n" . print_r($html, true), 3, 'auit.log');
        return $html;
    }
    protected function renderTotalTable($document,$layoutInfo)
    {
        $template = $this->getTemplateInfo($document);
        if (!$template || $template->isTemplate(\Snmportal\Pdfprint\Model\Template::TYPE_SHIPPING))
            return '';
        $html='';
        $html.='<div class="table-totals-frame"><table  class="table-totals"  cellpadding="0" cellspacing="0">';
        $html.='<tfoot>';

        //$this->_scopeConfig->getValue()

        $table_totals = $template->getData('table_totals');
        // Use Custom Totals
        if ( $template->getData('table_totals_use_custom') && is_array($table_totals) && count($table_totals) )
        {
            $order = $this->getDocumentOrder($document);
            $totals = $this->_getTotalsList();

            foreach ( $table_totals as $totalDef )
            {
                foreach ($totals as $total) {
                    $total->setOrder($order)->setSource($document);
                    $totalField = $total->getSourceField();
                    if ( $totalDef['type'] == $totalField )
                    {
                        if ( $totalDef['visible'] == 'always' || $total->canDisplay() )
                        {
                            if ( $totalField == 'tax_amount' && !$template->getData('table_taxrenderer_default') )
                            {
                                // Fix for PDF renderer multiple invoices
                                $html.= $this->_templateHelper->renderTaxTotals($document,$order,$template);
                            }
                            else {
                                $totalsForDisplay = $total->getTotalsForDisplay();
                                foreach ($totalsForDisplay as $idx => $totalData) {
                                    $name = rtrim($totalData['label'],':');
                                    $name = $template->translateValue($name,'label');
                                    $name = str_replace('()','',$name);
                                    $html.='<tr class="'.$totalField.'">';
                                    $html.='<td class="first"></td>';
                                    $html.='<td class="label">'.$name.'</td>';
                                    $html.='<td class="amount">'.$template->translateValue($totalData['amount'],'value').'</td>';
                                    $html.='</tr>';
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
        else {
            foreach ( $layoutInfo['totals'] as $total )
            {
                $html.='<tr class="'.$total['class'].'">';
                $html.='<td class="first"></td>';
                foreach ( $total['cols'] as $idx => $col ) {
                    $html.='<td class="'.$col['class'].'">';

                    $html.= $template->translateValue($col['valueHTML'],$idx?'value':'label');
                    $html.='</td>';
                }
                $html.='</tr>';
            }
        }
        $html.='</tfoot></table></div>';
        return $html;
    }
    protected function stripSpecialTags($str)
    {
        $str = preg_replace('/<script(?:\s[^>]*)?>([^<]+)<\/script>/i', '', $str);
        $str = preg_replace('/<a(?:\s[^>]*)class=\'([^\']*)\'[^>]*>([^<]+)<\/a>/i', '<span class="anchor \\1">\\2</span>', $str);
        $str = preg_replace('/<a(?:\s[^>]*)class="([^"]*)"[^>]*>([^<]+)<\/a>/i', '<span class="anchor \\1">\\2</span>', $str);
        return $str;
    }
    protected function colDataTableValue($document,$row,$col)
    {
        foreach ( $row['cols']  as $rcol )
        {
            if ( $rcol['class'] == $col['class'] )
                return $this->stripSpecialTags($rcol['valueHTML']);
        }
        return '';//'Data for '.$col['class'].' not found';
    }
    protected function getFrontendValue($row,$token)
    {
        foreach ( $row['cols']  as $rcol )
        {
            if ( $rcol['type'] == $token )
                return $this->stripSpecialTags($rcol['valueHTML']);
        }
        return '';
    }
    protected function getDataTableValue($item,$document,$row,$col)
    {
        /** @var $item \Magento\Sales\Model\Order\Item */
        /** @var $document \Magento\Sales\Model\Order */
        /** @var $block \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer */
        $order = $this->getDocumentOrder($document);
        if ( isset($col['type']) )
            switch ( $col['type'] )
            {
                case 'position':
                    return $row['idx'];
                    break;
                case 'fr_product':
                    //<strong class="product name product-item-name">Erika Running Short</strong><dl class="item-options"><dt>Color</dt><dd>Black</dd><dt>Size</dt><dd>55 cm</dd></dl>
                    $v = $this->getFrontendValue($row,'fr_name');

                    return $v;
                    break;
                case 'fr_sku':
                    $v = $this->getFrontendValue($row,'fr_sku');
                    return $v;
                    break;
                case 'fr_options':
                    $v = $this->getFrontendValue($row,'fr_options');
                    return $v;
                    break;
                case 'fr_product_sku':
                    $v = $this->getFrontendValue($row,'fr_name');
                    $v .= '<div class="sku">'.$this->getFrontendValue($row,'fr_sku').'</div>';
                    return $v;
                    break;
                case 'fr_price':
                    return ''.$this->getFrontendValue($row,'fr_price');
                    break;
                case 'price_excl_tax':
                    if ($item )
                    {
                        return  ''.$order->formatPriceTxt($item->getPrice());
                        //return  ''.$document->formatPricePrecision($item->getPrice(), 2, true);
                    }
                    break;
                case 'price_incl_tax':
                    if ($item )
                    {
                        return ''.$order->formatPriceTxt($item->getPriceInclTax());
                    }
                    break;
                case 'price_original':
                    if ($item )
                    {
                        return ''.$order->formatPriceTxt($item->getOriginalPrice());
                    }
                    break;
                case 'fr_qty':
                    $v = $this->getFrontendValue($row,'fr_qty');
                    //<ul class="items-qty"><li class="item"><span class="title">Bestellt</span><span class="content">22</span></li></ul>
                    return $v;
                    break;
                case 'qty':
                    if ($item )
                    {
                        return $item->getQty()*1;
                    }
                    break;
                case 'qty_ordered':
                    if ($item )
                    {
                        return $item->getQtyOrdered()*1;
                    }
                    break;
                case 'qty_shipped':
                    if ($item )
                    {
                        return $item->getQtyShipped()*1;
                    }
                    break;
                case 'qty_canceled':
                    if ($item )
                    {
                        return $item->getQtyCanceled()*1;
                    }
                    break;
                case 'qty_refunded':
                    if ($item )
                    {
                        return $item->getQtyRefunded()*1;
                    }
                    break;
                case 'qty_invoiced':
                    if ($item )
                    {
                        return $item->getQtyInvoiced()*1;
                    }
                    break;
                case 'subtotal_incl_tax':
                    if ( $item && $this->helperBlock ) {
                        return $this->helperBlock->displayPrices(
                            $this->_checkoutHelper->getBaseSubtotalInclTax($item),
                            $this->_checkoutHelper->getSubtotalInclTax($item)
                        );
                    }
                    return '';
                    break;
                case 'fr_row_total':
                    return ''.$this->getFrontendValue($row,'fr_subtotal');
                    break;
                case 'row_total_excl_tax':
                    return  ''.$order->formatPriceTxt($item->getRowTotal());
                    break;
                case 'row_total_incl_tax':
                    return  ''.$order->formatPriceTxt($item->getRowTotalInclTax());
                    break;
                case 'tax_amount':
                    if ( $item && $this->helperBlock ) {
                        return $this->helperBlock->displayPriceAttribute('tax_amount');
                        //return $this->displayPriceAttribute($document,$item,'tax_amount');
                    }
                    break;
                case 'tax_percent':
                    if ( $item && $this->helperBlock ) {
                        if (is_null($item->getTaxPercent()) && $item->getOrderItem()) {
                            return $this->helperBlock->displayTaxPercent($item->getOrderItem());
                        }
                        return $this->helperBlock->displayTaxPercent($item);
                    }
                    return '';
                    break;
                case 'discount_amount':
                    if ( $item && $this->helperBlock) {
                        return $this->helperBlock->displayPriceAttribute('discount_amount');
                    }
                    break;

                case 'status':
                    return $item->getStatus();
                    break;
                case 'image':
                    if ($path = $this->_templateHelper->getProductImage($item))
                        return '<img class="product-img" src="' . $this->_mediaDirectory->getAbsolutePath($path).'" />';
                    return '';
                    break;
                case 'custom':
                    //$block->displayPriceAttribute('discount_amount')
                    return 'not defined';
                    break;
                default:
                    break;
            }
        if ( isset($col['type']) )
            return 'Data for '.$col['type'].' not found';
        return '';
    }

    protected function getDataTableCols($document)
    {
        $cols = $this->getTemplateInfo($document)->getTableColumns();
        if ( $this->getTemplateInfo($document)->getData('table_columns_use_default') && is_array($cols) && count($cols) )
            return $cols;
        return null;
    }
    protected function getPdfTemplate($document){
        if ( $this->getTemplateInfo($document) )
            return $this->getTemplateInfo($document)->getFullPath('pdf_background');
        return false;
    }

    protected function getColTypeIndex($index)
    {
        $types =['name','sku','price','qty','subtotal','options'];

        foreach ($types  as $idx => $type )
        {
            if ( $idx == $index )
            {

                return 'fr_'.$type;
            }

        }
        return 'fr_';
    }
    protected function getColType($class)
    {
        $types =['name','sku','price','qty','subtotal','options'];

        foreach ($types  as $type )
        {
            if ( strpos($class,$type) !== false )
            {

                return 'fr_'.$type;
            }

        }
        return 'fr_';
    }
    protected function parseLayout($html)
    {
//        error_log("\n" . print_r($html, true), 3, 'auit.log');

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $doc = @\DOMDocument::loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . "\n".'<html><body>'.$html.'</body></html>');
        $xpath = new \DOMXpath($doc);
        $tables = $xpath->query('//table[contains(@class,"table-order-items")]');
        $result=array('cols'=>array(),'rows'=>array(),'totals'=>array());
        if ( $tables->length )
        {
            $ths = $xpath->query('//thead//th',$tables[0]);
            foreach ( $ths as $th ) {
                $cl = $th->getAttribute('class');
                if ( $this->document instanceof \Magento\Sales\Model\Order\Shipment && $cl == 'col price')
                {
                    // BUG Header has price not qty class
                    $cl = 'col qty';
                }
                $result['cols'][] = array(
                    'class' => $cl,
                    'type' => $this->getColType($cl),
                    'value' => $this->trimNodeValue($th),
                    'valueHTML' => $this->trimHTML($this->DOMinnerHTML($th))
                );
            }
            $trs = $xpath->query('//tbody//tr',$tables[0]);
            $lastItemId = 0;
            foreach ( $trs as $tr) {
                $tds = $xpath->query('td',$tr);
                $row=array();
                $row['class']=$tr->getAttribute('class');


                if ( $tr->getAttribute('id') )
                {
                    $row['id']=$tr->getAttribute('id');
                    $x = explode('-',$tr->getAttribute('id'));
                    $row['itemid']=$x[count($x)-1];
                    $lastItemId = $row['itemid'];
                    $row['special']=false;
                } else {
                    $row['special']=true;
                    $row['itemid']=$lastItemId;
                }

                foreach ( $tds as $index => $td) {
                    $row['cols'][] = array(
                        'class' => $td->getAttribute('class'),
                        'type' => $this->getColTypeIndex($index),
                        'value' => $this->trimNodeValue($td),
                        'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                    );
                }
/*

                if ( $row['id'] )
                {
                    $row['special']=false;
                    $x = explode('-',$tr->getAttribute('id'));
                    $row['itemid']=$x[count($x)-1];
                    $lastItemId = $row['itemid'];
                    foreach ( $tds as $td) {
                        $row['cols'][] = array(
                            'class' => $td->getAttribute('class'),
                            'type' => $this->getColType($td->getAttribute('class')),
                            'value' => $this->trimNodeValue($td),
                            'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                        );
                    }
                }else { //Special Rows Gift,...
                    $row['itemid']=$lastItemId;
                    $row['special']=true;
                    foreach ( $tds as $td) {
                        $row['cols'][] = array(
                            'class' => $td->getAttribute('class'),
                            'type' => $this->getColType($td->getAttribute('class')),
                            'value' => $this->trimNodeValue($td),
                            'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                        );
                    }
                }
*/
                $result['rows'][] = $row;
            }
            $trs = $xpath->query('//tfoot//tr',$tables[0]);
            foreach ( $trs as $tr) {
                $tds = $xpath->query('*',$tr);
                $row=array();
                $row['class']=$tr->getAttribute('class');
                foreach ( $tds as $td) {
                    $row['cols'][] = array('class' => $td->getAttribute('class'),
                        'value' => $this->trimNodeValue($td),
                        'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                    );
                }
                $result['totals'][] = $row;
            }
        }
        return $result;
    }


}
