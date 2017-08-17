<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class MassEnable
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Controller\ResultFactory;

class MassEnable extends Index
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_feeCollectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $fee) {
            $fee->setData('enabled', 1)
                ->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been changed.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}