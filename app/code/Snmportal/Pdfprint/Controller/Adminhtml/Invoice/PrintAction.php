<?php
namespace  Snmportal\Pdfprint\Controller\Adminhtml\Invoice;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\InvoiceFactory
     */
    protected $invoicePdfFactory;

    /**
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_pdfHelper;

    /**
     * @param \Magento\Backend\App\Action\Context                $context
     * @param \Magento\Framework\App\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface        $orderRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface  $invoiceRepository,
        \Snmportal\Pdfprint\Model\Pdf\InvoiceFactory $invoicePdfFactory,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePdfFactory = $invoicePdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }


    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $Id = $this->getRequest()->getParam('invoice_id');
        $tplId = $this->getRequest()->getParam('tplid');
        if ($Id) {
            $invoice = $this->invoiceRepository->get($Id);
            if ($invoice) {
                return $this->_pdfHelper->downloadPDF($this->invoicePdfFactory->create()->setRenderTemplateId($tplId),$invoice);
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/view');
    }
}
