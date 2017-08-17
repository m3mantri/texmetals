<?php
namespace  Snmportal\Pdfprint\Controller\Adminhtml\Creditmemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\CreditmemoFactory
     */
    protected $creditmemoPdfFactory;

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
        \Magento\Sales\Api\CreditmemoRepositoryInterface  $creditmemoRepository,
        \Snmportal\Pdfprint\Model\Pdf\CreditmemoFactory $creditmemoPdfFactory,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoPdfFactory = $creditmemoPdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }


    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $Id = $this->getRequest()->getParam('creditmemo_id');
        $tplId = $this->getRequest()->getParam('tplid');
        if ($Id) {
            $creditmemo = $this->creditmemoRepository->get($Id);
            if ($creditmemo) {
                return $this->_pdfHelper->downloadPDF($this->creditmemoPdfFactory->create()->setRenderTemplateId($tplId),$creditmemo);
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/view');
    }
}
