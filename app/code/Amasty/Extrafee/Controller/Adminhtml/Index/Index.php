<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class Index
 *
 * @author Artem Brunevski
 */

class Index extends \Amasty\Extrafee\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->_resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Amasty_Extrafee::fee_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Extra Fees'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Extra Fees'), __('Extra Fees'));
        $resultPage->addBreadcrumb(__('Manage Extra Fees'), __('Manage Extra Fees'));

        return $resultPage;
    }
}