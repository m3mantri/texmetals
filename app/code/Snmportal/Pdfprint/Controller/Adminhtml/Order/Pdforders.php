<?php
namespace Snmportal\Pdfprint\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

//class Pdforders extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
class Pdforders extends \Magento\Backend\App\Action
{


    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    protected $filter;
    /**
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_pdfHelper;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory           $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Snmportal\Pdfprint\Model\Pdf\OrderFactory $orderPdfFactory,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->date = $date;
        $this->_pdfHelper = $pdfHelper;
        parent::__construct($context);//, $filter);
        $this->filter = $filter;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }

    /**
     * Print selected orders
     *
     * @param AbstractCollection $collection
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        if (!isset($pdf)) {
            $pdf = $this->orderPdfFactory->create()->getPdf($collection);
        } else {
            $pages = $this->orderPdfFactory->create()->getPdf($collection);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
        }
        $date = $this->date->date('Y-m-d_H-i-s');

        return $this->fileFactory->create(
            'orders' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
    public function execute()
    {
        $cc = $this->collectionFactory->create();
        $collection = $this->filter->getCollection($cc);
        //$this->context->getPageLayout();
        $pdf = $this->orderPdfFactory->create()->getPdf($collection);
        $date = $this->date->date('Y-m-d_H-i-s');
        return $this->fileFactory->create(
            'orders' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
