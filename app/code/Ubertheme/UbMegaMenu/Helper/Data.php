<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Data Helper
 *
 */
class Data extends AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Application config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfig;

    /**
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     *
     * @var \Ubertheme\UbMegaMenu\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     *
     * @var \Ubertheme\UbMegaMenu\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Config\Model\Config $configModel
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Ubertheme\UbMegaMenu\Model\GroupFactory $groupFactory,
        \Ubertheme\UbMegaMenu\Model\ItemFactory $itemFactory
    )
    {
        $this->_context = $context;
        $this->_storeManager = $storeManager;
        $this->_appConfig = $config;
        $this->_categoryFactory = $categoryFactory;
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
        $this->_groupFactory = $groupFactory;
        $this->_itemFactory = $itemFactory;

        parent::__construct($context);
    }

    public function getConfigValue($key = null, $data = [])
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\StoreManagerInterface $manager */
        $manager = $om->get('\Magento\Framework\App\ScopeResolverInterface');
        $scopeCode = $manager->getScope()->getCode();

        $currentStoreCode = $this->_storeManager->getStore()->getCode();
        $currentWebsiteCode = $this->_storeManager->getWebsite()->getCode();

        if ($scopeCode == $currentStoreCode) {
            $scope = ScopeInterface::SCOPE_STORES;
        } elseif ($scopeCode == $currentWebsiteCode) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
        } else {
            $scope = 'default';
            //$scopeId = 0;
            $scopeCode = '';
        }

        $sections = ['ubmegamenu'];
        $value = null;
        if (isset($data[$key])) {
            $value = $data[$key];
        } else {
            foreach ($sections as $section) {
                $groups = $this->_appConfig->getValue($section, $scope, $scopeCode);
                if ($groups) {
                    foreach ($groups as $configs) {
                        if (isset($configs[$key])) {
                            $value = $configs[$key];
                            break;
                        }
                    }
                }
                if ($value)
                    break;
            }
        }

        return $value;
    }

    public function getCategoryOptions($storeId = null, $isFilter = false, $countProduct = false)
    {
        $store = $this->getStore($storeId);
        $parent_id = $store->getRootCategoryId();
        if ($store->getId() == Store::DEFAULT_STORE_ID) {
            $defaultStoreItems = $this->_categoryFactory->create()->getCollection()
                ->addFieldToFilter('parent_id', ['in' => [$parent_id]]);
            $parent_id = $defaultStoreItems->getFirstItem()->getId();
        }

        //get categories
        $categories = $this->getCategories($store->getId(), $parent_id);

        //build tree options
        $options = $this->buildTree($parent_id, $categories, 99, 'name', 'entity_id', 'parent_id', $isFilter, $countProduct);

        return $options;
    }

    public function getCMSPageOptions($storeIds = [], $isFilter = false)
    {
        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->_pageFactory->create()->getCollection()
            ->addFieldToSelect(['page_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    public function getStaticBlockOptions($storeIds = [])
    {
        $options = [];

        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->_blockFactory->create()->getCollection()
            ->addFieldToSelect(['block_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    public function getCategories($storeId = 0, $parentId = 0)
    {
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToSelect(['entity_id', 'parent_id', 'name', 'level'])
            ->setStoreId($storeId)
            ->addIsActiveFilter();

        if ($parentId) {
            $collection->addFieldToFilter('path', ['like' => '%' . $parentId . '/%']);
        }

        $collection->getSelect()->order('position ASC');

        return $collection->load();
    }

    public function getMenuGroupById($menuId)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_groupFactory->create()->getCollection()
            ->addFieldToSelect(['group_id', 'title', 'identifier', 'animation_type', 'is_active'])
            ->addFieldToFilter('group_id', ['eq' => $menuId])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $collection->getFirstItem();
    }

    public function getMenuGroupByKey($menuKey)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_groupFactory->create()->getCollection()
            ->addFieldToSelect(['group_id', 'title', 'identifier', 'animation_type', 'is_active'])
            ->addFieldToFilter('identifier', ['eq' => $menuKey])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $collection->getFirstItem();
    }

    public function getMenuItems($menuGroupId)
    {
        $items = null;
        if ($menuGroupId) {
            $collection = $this->_itemFactory->create()->getCollection()
                ->addFieldToFilter('group_id', ['eq' => $menuGroupId])
                ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Item::STATUS_ENABLED])
                ->addOrder('level', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
                ->addOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
            $items = $collection->getItems();
        }

        return $items;
    }

    /**
     * Build tree items function
     *
     * @param int $rootId
     * @param $models
     * @param int $maxLevel
     * @param string $labelField
     * @param string $keyField
     * @param string $parentField
     * @param bool|false $isFilter
     * @param bool|false $countProduct
     * @return array
     */

    public function buildTree($rootId = 0, $models, $maxLevel = 99, $labelField = "name", $keyField = "entity_id", $parentField = "parent_id", $isFilter = false, $countProduct = false)
    {
        //grouping
        @$children = [];
        foreach ($models as $model) {
            $pt = $model->getData($parentField);
            $list = (isset($children[$pt]) && $children[$pt]) ? $children[$pt] : [];
            array_push($list, $model);
            $children[$pt] = $list;
        }

        //build tree
        $lists = $this->_toTree($rootId, '', [], $children, $maxLevel, 0, $labelField, $keyField, $parentField, $countProduct);


        if ($isFilter) {
            $outputs = ['0' => __('All')];
        }

        foreach ($lists as $id => $list) {
            $lists[$id]->$labelField = $lists[$id]->$labelField;
            $outputs[$lists[$id]->getData($keyField)] = $lists[$id]->$labelField;
        }
        return $outputs;
    }

    /**
     * Generate tree items
     *
     * @param $id
     * @param $indent
     * @param $list
     * @param $children
     * @param int $maxLevel
     * @param int $level
     * @param $label
     * @param $key
     * @param $parent
     * @param bool|false $countProduct
     * @return mixed
     */
    protected function _toTree($id, $indent, $list, &$children, $maxLevel = 99, $level = 0, $label, $key, $parent, $countProduct = false)
    {
        if (@$children[$id] && $level <= $maxLevel) {

            foreach ($children[$id] as $v) {
                $id = $v->getData($key);

                $pre = '';
                $spacer = '--- ';
                if ($v->getData($parent) == 0) {
                    $txt = $v->getData($label);
                } else {
                    $txt = $pre . $v->getData($label);
                }

                $list[$id] = $v;
                $list[$id]->$label = "{$indent}{$txt}";

                if ($countProduct) {
                    $list[$id]->$label .= " (" . $v->getProductCount() . ")";
                }

                //$list[$id]->children = count(@$children[$id]);
                $list = $this->_toTree($id, $indent . $spacer, $list, $children, $maxLevel, $level + 1, $label, $key, $parent, $countProduct);
            }
        }
        return $list;
    }

    protected function getStore($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        }

        return $this->_storeManager->getStore($storeId);
    }

    protected function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');
        return $context->getRequest();
    }
}
