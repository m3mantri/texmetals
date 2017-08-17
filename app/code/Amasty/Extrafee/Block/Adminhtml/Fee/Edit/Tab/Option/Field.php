<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Adminhtml\Fee\Edit\Tab\Option;

/**
 * Class Field
 *
 * @author Artem Brunevski
 */
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Amasty\Extrafee\Controller\RegistryConstants;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Amasty\Extrafee\Model\Fee;
use Amasty\Extrafee\Model\Fee\Source\PriceType;

class Field extends Widget implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'fee/options.phtml';

    /** @var Registry  */
    protected $_registry;

    /** @var PriceType  */
    protected $_priceType;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PriceType $priceType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PriceType $priceType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_priceType = $priceType;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @return array|mixed
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }
                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }
        return $stores;
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    /**
     * @return array|mixed
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            /** @var \Amasty\Extrafee\Model\Fee $model */
            $model = $this->_registry->registry(RegistryConstants::FEE);
            $values = [];

            $options = $model->getOptions();

            if (is_array($options)) {
                foreach ($model->getOptions() as $order => $option) {

                    $storesData = [];

                    foreach ($this->getStores() as $store) {
                        $storesData['store' . $store->getId()] = array_key_exists('options', $option) &&
                        array_key_exists($store->getId(), $option['options']) ?
                            $option['options'][$store->getId()] :
                            '';
                    }

                    $storesData = array_merge($storesData, [
                        'checked' => array_key_exists('default', $option) && $option['default'] ? 'checked="checked"' : '',
                        'price' => array_key_exists('price', $option) && $option['price'] ? $option['price'] : '',
                        'price_type' => array_key_exists('price_type', $option) && $option['price_type'] ? $option['price_type'] : Fee::PRICE_TYPE_FIXED,
                        'intype' => 'radio',
                        'id' => $option['entity_id'],
                        'sort_order' => $option['order']
                    ]);

                    $values[] = $storesData;
                }
            }

            $this->setData('option_values', $values);
        }
        return $values;
    }

    /**
     * @return array
     */
    public function getPriceTypes()
    {
        return $this->_priceType->toOptionArray();
    }
}