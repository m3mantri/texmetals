<?php
/**
 * Solwin Infotech
 * Solwin Previous Next Products Extension
 *
 * @category   Solwin
 * @package    Solwin_PrevNext
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\PrevNext\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $_coreRegistry;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    
    /**
     * @param  \Magento\Framework\Registry $registry
     * @param  \Magento\Framework\App\Helper\Context $context 
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
    }
    
    /**
     * Return  config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key)
    {
        $result = $this->scopeConfig->getValue($key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $result;
    }
    
    /* 
     * Get previous product
     */
    public function getPreviousProduct() {
        $proid = $this->_coreRegistry->registry('current_product')->getId();
       
        $looping = $this->getConfig(
                'prevnextsection/prevnextgroup/loopproducts'
                );
        $sortby = $this->getConfig(
                'prevnextsection/prevnextgroup/sortbyproduct'
                );
        $orderby = $this->getConfig(
                'prevnextsection/prevnextgroup/orderbyproduct'
                );
        $positions = [];
        
        if ($this->_coreRegistry->registry('current_category')) {
            $currentCategory = $this->_coreRegistry
                    ->registry('current_category');
            if (get_class($currentCategory->getResource()) != 
                    '\Mage\Catalog\Model\Resource\Category\Flat') {
                $positions = $currentCategory
                        ->getProductCollection()
                        ->addAttributeToSort($sortby, $orderby)
                        ->getColumnValues('entity_id');
            }
        }
       
        if (empty($positions)) {
            $positions = [];
        }
        $cpk = array_search($proid, $positions);
        if ($looping == 1) {
            $arrPos = array_slice($positions, 0, $cpk);
            $arrCnt = array_slice($positions, $cpk, count($positions));
            $arr = array_merge($arrCnt, $arrPos);
            $slice = array_reverse($arr);
        } else {
            $slice = array_reverse(array_slice($positions, 0, $cpk));
        }

        foreach ($slice as $productId) {
            $prevproduct = $this->getNextPrevProduct1($productId);
            return $prevproduct;
        }
        return false;
    }
    
    /* 
     * Get current category id
     */
    public function getCurrentCategoryId() {
        $catid = $this->_coreRegistry->registry('current_category')->getId();
        if (isset($catid)) {
            return $catid;
        } else {
            return 0;
        }
    }
    
    /* 
     * Get current category url
     */
    public function getCurrentCategoryUrl() {
        $catUrl = $this->_coreRegistry->registry('current_category')->getUrl();
        if (isset($catUrl)) {
            return $catUrl;
        } else {
            return '';
        }
    }
    /* 
     * Get current category url
     */
    public function getCurrentCategoryName() {
        $catUrl = $this->_coreRegistry->registry('current_category')->getName();
        if (isset($catUrl)) {
            return $catUrl;
        } else {
            return '';
        }
    }
    
    /*
     * Get current category thumbnail
     */
    public function getCurrentCategoryThumbnail() {
        $catThumb = $this->_coreRegistry
                ->registry('current_category')
                ->getCategoryThumb();
        if (isset($catThumb)) {
            return $catThumb;
        } else {
            return '';
        }
    }
    
    /* 
     * Get next product
     */
    public function getNextProduct() {
        $proid = $this->_coreRegistry->registry('current_product')->getId();
        $looping = $this->getConfig(
               'prevnextsection/prevnextgroup/loopproducts'
               );
        $sortby = $this->getConfig(
                'prevnextsection/prevnextgroup/sortbyproduct'
                );
        $orderby = $this->getConfig(
                'prevnextsection/prevnextgroup/orderbyproduct'
                );
        $positions = [];
        if ($this->_coreRegistry->registry('current_category')) {

            $currentCategory = $this->_coreRegistry
                    ->registry('current_category');
            if (get_class($currentCategory->getResource()) != 
                    '\Mage\Catalog\Model\Resource\Category\Flat') {
                $positions = $currentCategory
                        ->getProductCollection()
                        ->addAttributeToSort($sortby, $orderby)
                        ->getColumnValues('entity_id');
            }
        }

        if (empty($positions)) {
            $positions = [];
        }

        $cpk = array_search($proid, $positions);

        if ($looping == 1) {
            $arrPos = array_slice($positions, 0, $cpk + 1);
            $arrCnt = array_slice($positions, $cpk + 1, count($positions));
            $arr = array_merge($arrCnt, $arrPos);
            $nextslice = $arr;
        } else {
            $nextslice = array_slice($positions, $cpk + 1, count($positions));
        }


        foreach ($nextslice as $nextId) {
            $nextproduct = $this->getNextPrevProduct1($nextId);
            return $nextproduct;
        }

        return false;
    }

    /* 
     * Get first product
     */
    public function getNextPrevProduct1($id = 0) {

        $collection = $this->_productFactory->create()
                ->getCollection()->addAttributeToSelect(
                        'name'
                )->addAttributeToSelect(
                        'sku'
                )->addAttributeToSelect(
                        'image'
                )
                ->addFieldToFilter('entity_id', ['in' => $id]);

        return $collection;
    }

    /**
     * Get base url with store code
     */
    public function getBaseUrlWithStoreCode() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get base url without store code
     */
    public function getBaseUrl() {
        return $this->_storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

}