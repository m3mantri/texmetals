<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Sellback;

/**
 * Class Index
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Sellback
 */
class Index extends \Aheadworks\Helpdesk\Controller\Adminhtml\Sellback
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
        $resultPage->setActiveMenu('Aheadworks_Helpdesk::sellback');
        $resultPage->getConfig()->getTitle()->prepend(__('Sell Back'));

        return $resultPage;
    }
}