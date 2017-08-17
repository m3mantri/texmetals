<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class Delete
 *
 * @author Artem Brunevski
 */

class Delete extends Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $fee = $this->initCurrentFee();
            $this->_feeRepository->delete($fee);
            $this->messageManager->addSuccessMessage(__('The fee has been deleted.'));
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            return $resultRedirect->setPath('*/*/edit', ['id' => $fee->getId()]);
        }
    }
}