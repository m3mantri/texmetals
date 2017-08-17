<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class MassDisable
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Controller\ResultFactory;

class MassDisable extends Index
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_feeCollectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $fee) {
            $fee->setData('enabled', 0)
                ->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been changed.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}