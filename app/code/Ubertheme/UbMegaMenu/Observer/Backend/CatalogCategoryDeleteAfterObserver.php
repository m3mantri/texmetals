<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogCategoryDeleteAfterObserver implements ObserverInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Ubertheme\UbMegaMenu\Helper\Data $helper
     */
    public function __construct(\Ubertheme\UbMegaMenu\Helper\Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Delete related menu item after a category deleted
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        //find menu item related with this category to delete
        $relatedMenuItem = $this->getRelatedMenuItems($om, [$category->getId()], true);
        if ($relatedMenuItem->getId()) {
            /* @var \Ubertheme\UbMegaMenu\Model\Item $relatedMenuItem */
            $relatedMenuItem->delete();
        }

        return $this;
    }

    public function getRelatedMenuItems($om, $categoryIds, $getSingle = false)
    {
        $rs = null;
        /** @var \Ubertheme\UbMegaMenu\Model\ItemFactory $itemFactory */
        $itemFactory = $om->get('\Ubertheme\UbMegaMenu\Model\ItemFactory');
        $collection = $itemFactory->create()->getCollection()
            ->addFieldToSelect(['item_id', 'group_id', 'category_id', 'link'])
            ->addFieldToFilter('link_type', ['eq' => 'category-page'])
            ->addFieldToFilter('category_id', ['in' => [$categoryIds]])
            ->addOrder('level', 'ASC')
            ->load();
        if ($getSingle) {
            $rs = $collection->getFirstItem();
        } else {
            $rs = $collection->getItems();
        }

        return $rs;
    }
}
