<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Sellbacklimitorder;

/**
 * Class Index
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Sellbacklimitorder
 */
class Index extends \Aheadworks\Helpdesk\Controller\Adminhtml\Sellbacklimitorder
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
        $resultPage->setActiveMenu('Aheadworks_Helpdesk::sellbacklimitorder');
        $resultPage->getConfig()->getTitle()->prepend(__('Sell Back Limit Order'));

        return $resultPage;
    }
}