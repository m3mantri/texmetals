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

namespace Solwin\PrevNext\Block;

use Magento\Framework\View\Element\Template;

class PrevNext extends Template
{
    
    /**
     * @var \Magento\Framework\Registry    
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;
    
    /**
     * @param  \Magento\Framework\View\Asset\Repository $assetRepo
     * @param  \Magento\Catalog\Model\Product $product                   
     * @param  \Magento\Catalog\Block\Product\Context $context 
     * @param   array $data
     */

    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->_catalogProduct = $product;
        $this->_coreRegistry = $context->getRegistry();
     
        parent::__construct($context, $data);
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
        return $this->_storeManager->
                getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    
    /* 
     * Get current category
     */
    public function getCategory() {
        return $this->_coreRegistry->registry('current_category');
    }
    
    /* 
     * Set product
     */
    public function setProduct(\Magento\Catalog\Model\Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }
    
    /* 
     * Get product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            if ($this->_coreRegistry->registry('current_product')) {
                $this->_product = $this->_coreRegistry
                        ->registry('current_product');
            } else {
                $this->_product = $this->_catalogProduct;
            }
        }
        return $this->_product;
    }

}
