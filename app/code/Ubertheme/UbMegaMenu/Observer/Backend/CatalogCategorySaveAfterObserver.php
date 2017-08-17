<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogCategorySaveAfterObserver implements ObserverInterface
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
     * Update related menu items after a category saved
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();

        if ($category->getParentId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            return;
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $parentId = $category->getParentId();

        //check exists of menu item with parent category
        $parentMenuItem = $this->getRelatedMenuItems($om, [$parentId], true);
        if ($parentMenuItem->getId()) {
            //check exists of menu item with this category
            $relatedMenuItem = $this->getRelatedMenuItems($om, [$category->getId()], true);
            if (!$relatedMenuItem->getId()) {
                //add new menu item with this category
                $this->addMenuItem($om, $parentMenuItem, $category);
            }
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

    /**
     * @param \Ubertheme\UbMegaMenu\Model\Item $parentMenuItem
     * @param \Magento\Catalog\Model\Category\Interceptor $category
     */
    public function addMenuItem($om, $parentMenuItem, $category)
    {
        //build menu item data
        $data = [];
        $data['show_title'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES;
        $data['icon_image'] = '';
        $data['font_awesome'] = '';
        $data['target'] = '_self';
        $data['show_number_product'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG;
        $data['cms_page'] = null;
        $data['is_group'] = \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO;
        $data['mega_cols'] = 1;
        $data['mega_width'] = 0;
        $data['mega_col_width'] = 0;
        $data['mega_col_x_width'] = null;
        $data['mega_sub_content_type'] = \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS;
        $data['custom_content'] = null;
        $data['static_blocks'] = null;
        $data['addition_class'] = null;
        $data['description'] = null;
        $data['is_active'] = \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED;
        $data['sort_order'] = 0;
        /* @var \Ubertheme\UbMegaMenu\Model\Item $parentMenuItem */
        $data['parent_id'] = $parentMenuItem->getId();
        $data['group_id'] = $parentMenuItem->getGroupId();
        $data['link_type'] = \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY;
        $data['link'] = 'dynamically';
        $data['category_id'] = $category->getId();
        $data['title'] = $category->getName();
        $data['identifier'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($data['title'])), '-');

        //create and save menu item
        $menuItem = $om->create('Ubertheme\UbMegaMenu\Model\Item')->setData($data)->save();

        return $menuItem;
    }
}
