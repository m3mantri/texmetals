<?php

namespace VladimirPopov\AwHdIntegration\Controller\Adminhtml\Result;

use Magento\Framework\Controller\ResultFactory;

class MassConvertToTicket extends \Magento\Backend\App\Action
{
    const REDIRECT_URL = 'webforms/result/index';

    /**
     * Ticket Helper
     *
     * @var \VladimirPopov\AwHdIntegration\Helper\Ticket
     */
    private $ticketHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \VladimirPopov\AwHdIntegration\Helper\Ticket $ticketHelper
    )
    {
        $this->ticketHelper = $ticketHelper;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('webform_id')) {
            return $this->_authorization->isAllowed('VladimirPopov_WebForms::form' . $this->getRequest()->getParam('webform_id'));
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $Ids = $this->getRequest()->getParam('results');
        $departmentId = $this->getRequest()->getParam('department_id');
        $count = 0;
        if (!is_array($Ids) || empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            foreach ($Ids as $id) {
                $ticket = $this->ticketHelper->createFromResult($id, $departmentId);
                if($ticket->getId())
                    $count++;
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been converted to tickets.', $count));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL, ['webform_id' => $this->getRequest()->getParam('webform_id')]);
    }
}