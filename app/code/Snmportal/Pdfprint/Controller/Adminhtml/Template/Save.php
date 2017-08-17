<?php
namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Snmportal\Pdfprint\Logger\Logger
     */
    protected $_snmLogger;
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        \Snmportal\Pdfprint\Logger\Logger $snmLogger,
        Action\Context $context,
        PostDataProcessor $dataProcessor)
    {
        $this->_snmLogger = $snmLogger;
        $this->dataProcessor = $dataProcessor;
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            /** @var \Snmportal\Pdfprint\Model\Template $model */
            $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');

            $id = $this->getRequest()->getParam('template_id');
            if ($id) {
                $model->load($id);
            }
            //$this->_snmLogger->info('Current Object data before:',$model->getData());
            $model->setData($data);
            if ( $this->getRequest()->getParam('type', null) == 'import_file' )
            {
                $model->import();
            }

            $this->_eventManager->dispatch(
                'snmportal_pdfprint_template_prepare_save',
                ['model' => $model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['template_id' => $model->getId(), '_current' => true]);
            }

            try {
                $model->uploadSource();
                $model->save();
                $this->messageManager->addSuccess(__('You saved this template.'));
//                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($back=$this->getRequest()->getParam('back')) {
                       $this->_snmLogger->info('Back to :'.$back);
                    return $resultRedirect->setPath('*/*/'.$back, ['template_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the template.'));
            }

//            $this->_getSession()->setFormData($data);
            //return $resultRedirect->setPath('*/*/edit', ['page_id' => $this->getRequest()->getParam('template_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
