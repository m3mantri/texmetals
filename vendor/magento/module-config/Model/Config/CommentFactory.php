<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration comment model factory
 */
namespace Magento\Config\Model\Config;

class CommentFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param string $type
     * @return CommentInterface
     * @throws \InvalidArgumentException
     */
    public function create($type)
    {
        $commentModel = $this->_objectManager->create($type);
        if (!$commentModel instanceof CommentInterface) {
            throw new \InvalidArgumentException('Incorrect comment model provided');
        }
        return $commentModel;
    }
}
