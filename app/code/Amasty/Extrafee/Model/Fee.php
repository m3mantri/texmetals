<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class Fee
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\AbstractModel;
use Amasty\Extrafee\Api\Data\FeeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Amasty\Extrafee\Helper\Data as ExtrafeeHelper;

class Fee extends AbstractModel implements FeeInterface, IdentityInterface
{
    /**
     * Frontend types
     */
    const FRONTEND_TYPE_CHECKBOX = 'checkbox';
    const FRONTEND_TYPE_DROPDOWN = 'dropdown';
    const FRONTEND_TYPE_RADIO = 'radio';

    /**
     * Price types
     */
    const PRICE_TYPE_FIXED = 'fixed';
    const PRICE_TYPE_PERCENT = 'percent';

    /**
     * Fee cache tag
     */
    const CACHE_TAG = 'amasty_extrafee_fee';

    /** @var ExtrafeeHelper  */
    protected $extrafeeHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtrafeeHelper $extrafeeHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtrafeeHelper $extrafeeHelper,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->extrafeeHelper = $extrafeeHelper;
        $this->taxCalculation = $calculation;
        return parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


        /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Extrafee\Model\ResourceModel\Fee');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Get enabled
     * @return bool|null
     */
    public function getEnabled()
    {
        return parent::getData(self::ENABLED);
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return parent::getData(self::NAME);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return parent::getData(self::OPTIONS);
    }

    /**
     * @return mixed
     */
    public function getBaseOptions()
    {
        return parent::getData(self::BASE_OPTIONS);
    }

    /**
     * Get type
     * @return string
     */
    public function getFrontendType()
    {
        return parent::getData(self::FRONTEND_TYPE);
    }

    /**
     * Get current value
     * @return string
     */
    public function getCurrentValue()
    {
        return parent::getData(self::CURRENT_VALUE);
    }

    /**
     * @return mixed
     */
    public function getDiscountInSubtotal()
    {
        $value = parent::getData(self::DISCOUNT_IN_SUBTOTAL);

        if ($value === \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT){
            $value = $this->extrafeeHelper->getScopeValue(
                'calculation/discount_in_subtotal'
            );
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getTaxInSubtotal()
    {
        $value = parent::getData(self::TAX_IN_SUBTOTAL);

        if ($value === \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT){
            $value = $this->extrafeeHelper->getScopeValue(
                'calculation/tax_in_subtotal'
            );
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getShippingInSubtotal()
    {
        $value = parent::getData(self::SHIPPING_IN_SUBTOTAL);

        if ($value === \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT){
            $value = $this->extrafeeHelper->getScopeValue(
                'calculation/shipping_in_subtotal'
            );
        }

        return $value;
    }

    /**
     * @param $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @param string $description
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @param string $name
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @param array $options
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setOptions($options)
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * @param array $options
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setBaseOptions($options)
    {
        return $this->setData(self::BASE_OPTIONS, $options);
    }

    /**
     * @param string $frontendType
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setFrontendType($frontendType)
    {
        return $this->setData(self::FRONTEND_TYPE, $frontendType);
    }

    /**
     * @param mixed $currentValue
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setCurrentValue($currentValue)
    {
        return $this->setData(self::CURRENT_VALUE, $currentValue);
    }

    /**
     * @param mixed $discountInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setDiscountInSubtotal($discountInSubtotal)
    {
        return $this->setData(self::DISCOUNT_IN_SUBTOTAL, $discountInSubtotal);
    }

    /**
     * @param mixed $taxInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setTaxInSubtotal($taxInSubtotal)
    {
        return $this->setData(self::TAX_IN_SUBTOTAL, $taxInSubtotal);
    }

    /**
     * @param mixed $shippingInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setShippingInSubtotal($shippingInSubtotal)
    {
        return $this->setData(self::SHIPPING_IN_SUBTOTAL, $shippingInSubtotal);
    }

    /**
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function loadOptions()
    {
        return $this->getResource()->loadOptions($this);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float|int
     */
    protected function getTaxRate(\Magento\Quote\Model\Quote $quote)
    {
        $taxClass = $this->extrafeeHelper->getScopeValue('tax/tax_class');
        $taxRate = 0;

        if ($taxClass) {
            $rateRequest = $this->taxCalculation->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                $quote->getCustomerTaxClassId(),
                $quote->getStore(),
                $quote->getCustomerId()
            )->setProductClassId($taxClass);

            $taxRate = $this->taxCalculation->getRate($rateRequest);
        }

        return $taxRate;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return float|int
     */
    protected function getBaseQuoteTotal(
        \Magento\Quote\Model\Quote\Address\Total $total
    ){
        $baseQuoteTotals = $total->getBaseTotalAmount('subtotal');

        if ($this->getDiscountInSubtotal()){
            $baseQuoteTotals += $total->getBaseTotalAmount('discount');
        }

        if ($this->getTaxInSubtotal()){
            $baseQuoteTotals += $total->getBaseTotalAmount('tax');
        }

        if ($this->getShippingInSubtotal()){
            $baseQuoteTotals += $total->getBaseTotalAmount('shipping');
        }

        return $baseQuoteTotals;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetchBaseOptions(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ){
        $storeId = $quote->getStoreId();
        $rate = $quote->getBaseToQuoteRate();

        $options = [];

        $baseQuoteTotals = $this->getBaseQuoteTotal($total);

        $taxRate = $this->getTaxRate($quote);

        foreach($this->getOptions() as $idx => $item){

            /**
             * calculate base price
             */
            $basePrice = $item['price_type'] === self::PRICE_TYPE_FIXED ?
                floatval($item['price']) :
                floatval($item['price'] * $baseQuoteTotals / 100);

            $price = $basePrice * $rate;

            /**
             * apply tax class from module settings
             */
            if ($taxRate){
                $price += $price * $taxRate / 100;
                $basePrice += $basePrice * $taxRate / 100;
            }

            $options[] = [
                'index' => $item['entity_id'],
                'price' => $price,
                'base_price' => $basePrice,
                'default' => $item['default'],
                'label' => $this->getOptionLabel($storeId, $item['options'])
            ];
        }

        return $options;
    }

    /**
     * @param $storeId
     * @param array $values
     * @return string
     */
    protected function getOptionLabel($storeId, array $values)
    {
        $defaultLabel = array_key_exists(0, $values) ? $values[0] : '';

        return array_key_exists($storeId, $values) && $values[$storeId] !== '' ?
            $values[$storeId] :
            $defaultLabel;
    }

    /**
     * @param $storeId
     * @param $optionId
     * @return string
     */
    public function getStoreOptionLabel($storeId, $optionId)
    {
        $item = $this->getOption($optionId);

        return array_key_exists('options', $item)  ?
            $this->getOptionLabel($storeId, $item['options']) :
            '';
    }

    /**
     * @param $optionId
     * @return array
     */
    public function getOption($optionId)
    {
        $ret = [];
        foreach($this->getOptions() as $idx => $item) {
            if ($item['entity_id'] === $optionId) {
                $ret = $item;
                break;
            }
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function getOptionsIds()
    {
        $ids = [];
        foreach($this->getOptions() as $idx => $item) {
            $ids[] = $item['entity_id'];
        }
        return $ids;
    }
}