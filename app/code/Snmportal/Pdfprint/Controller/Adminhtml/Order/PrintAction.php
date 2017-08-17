<?php
namespace  Snmportal\Pdfprint\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\OrderFactory
     */
    protected $orderPdfFactory;

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
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Snmportal\Pdfprint\Model\Pdf\OrderFactory $orderPdfFactory,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->orderRepository = $orderRepository;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;

    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }


    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $tplId = $this->getRequest()->getParam('tplid');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                return $this->_pdfHelper->downloadPDF($this->orderPdfFactory->create()->setRenderTemplateId($tplId),$order);

            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/view');
    }
}
