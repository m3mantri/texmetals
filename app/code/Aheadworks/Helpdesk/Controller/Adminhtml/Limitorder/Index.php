<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Limitorder;

/**
 * Class Index
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Limitorder
 */
class Index extends \Aheadworks\Helpdesk\Controller\Adminhtml\Limitorder
{
    /**
     * Index action
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Aheadworks_Helpdesk::limitorder');
        $resultPage->getConfig()->getTitle()->prepend(__('Limit Order'));

        return $resultPage;
    }
}