<?php


namespace CP\Base\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $_weeeTax;

    /**
     * @param \Magento\Weee\Model\Tax $weeeTax
     */
    public function __construct(
        \Magento\Weee\Model\Tax $weeeTax,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->_weeeTax = $weeeTax;
        $this->_pricingHelper = $pricingHelper;
        $this->_taxHelper = $taxHelper;
    }

    /**
     * Customize range.
     *
     * @param array $list
     *
     * @return array
     */
    public function customizeRange($list)
    {
        ksort($list);
        $result = array();
        for ($index = 0; $index < count($list) ; $index++) {
            $key = $index + 1;
            if (array_key_exists($key, $list)) {
                $result[$list[$index]] = $list[$index] . '-' . ($list[$key] - 1);
            } else {
                $result[$list[$index]] = $list[$index] . '+';
            }
        }
        return $result;
    }

    /**
     * Retrieve qty list
     *
     * @param $tierPrices
     *
     * @return array
     */
    public function getQtyList($tierPrices)
    {
        $list = array();
        foreach ($tierPrices as $value) {
            $list[] = $value['price_qty'];
        }
        $list = $this->customizeRange($list);
        return $list;
    }

    /**
     * Get first range.
     *
     * @param $tierPrices
     *
     * @return int|string
     */
    public function getFirstRange($tierPrices)
    {
        if (!empty($tierPrices)) {
            $list = $this->getQtyList($tierPrices);

            $keys = array_keys($list);
            $val = array_shift($keys) - 1;
            if ($val == 1) {
                return 1;
            }
            return 1 . '-' . $val;
        }
        return 1 . '+';
    }

    /**
     * Build new range for tier prices.
     *
     * @param $tierPrices
     *
     * @return mixed
     */
    public function buildRange($tierPrices)
    {
        $list = $this->getQtyList($tierPrices);
        foreach ($tierPrices as $key => $value) {
            if (array_key_exists((string)$value['price_qty'], $list)) {
                $tierPrices[$key]['price_qty'] = $list[$value['price_qty']];
            }
        }
        return $tierPrices;
    }

    /**
     * get CC price for tier prices.
     *
     * @param $price
     *
     */
    public function getCcPrice($price, $cc = false)
    {
        if(!$cc) return $price;

        $percentage = 4;
        $percentageValue = ($percentage / 100) * $price;
        $ccPrice = $price + $percentageValue;

        return $ccPrice;
    }

    /**
     * custom from Magento 1
     * Adds HTML containers and formats tier prices accordingly to the currency used
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array                      $tierPrices
     * @param boolean                    $includeIndex
     * @return Mage_Weee_Helper_Data
     */
    public function processTierPrices($product, &$tierPrices, $includeIndex = true)
    {
        $weeeAmountInclTax = $this->_weeeTax->getWeeeAmount($product);
        $weeeAmount = $this->_weeeTax->getWeeeAmountExclTax($product);
        // $store = Mage::app()->getStore();
        foreach ($tierPrices as $index => &$tier) {
            $spanTag = '<span class="price tier-' . ($includeIndex ? $index : 'fixed');
            $html = $this->_pricingHelper->currency(
                $product->getPrice($product, $tier['website_price'], true) + $weeeAmountInclTax, true, false);
            $tier['formated_price_incl_weee'] = $spanTag . '-incl-tax">' . $html . '</span>';
            $html = $this->_pricingHelper->currency(
                $product->getPrice($product, $tier['website_price']) + $weeeAmount, true, false);
            $tier['formated_price_incl_weee_only'] = $spanTag . '">' . $html . '</span>';
            $tier['formated_weee'] = $this->_pricingHelper->currency($weeeAmount, true, false);
        }
        return $this;
    }
}
