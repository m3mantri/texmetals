<?php
namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;


/**
 * Class MassDelete
 */
class Importm1 extends \Snmportal\Pdfprint\Controller\Adminhtml\Template
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;
    protected $setupFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Snmportal\Pdfprint\Model\Template\SetupFactory $setupFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->setupFactory = $setupFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Snmportal\Pdfprint\Model\Template $model */
        $model = $this->setupFactory->create();
        try {
            if ( $model->importM1Data() )
                $this->messageManager->addSuccess(__('The Magento1 data has been imported.'));
            else {
                $this->messageManager->addWarning(__('No Magento1 data could be found. <a target="_blank" href="http://www.snm-portal.com/magento2_setup#importm1">Help</a>'));

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            //$this->messageManager->addException($e, __('Something went wrong while saving the template.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('index');
    }
}
