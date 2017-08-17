<?php

namespace MageArray\OrderComment\Controller\Adminhtml\Save;

use Magento\Backend\App\Action\Context;

class Comment extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->_objectManager = $context->getObjectManager();
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($post['oId']);
        $order->setOrderComment(strip_tags($post['comment']));
        $order->save();

    }

}
