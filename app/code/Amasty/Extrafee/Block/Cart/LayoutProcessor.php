<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Cart;

/**
 * Class LayoutProcessor
 *
 * @author Artem Brunevski
 */

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Extrafee\Model\Fee;
use Amasty\Extrafee\Model\FeesInformationManagement;
use Amasty\Extrafee\Helper\Data as ExtrafeeHelper;


class LayoutProcessor implements LayoutProcessorInterface
{
    /** @var FeesInformationManagement  */
    protected $feesInformationManagement;

    /** @var  CheckoutSession */
    protected $checkoutSession;

    /** @var ExtrafeeHelper  */
    protected $extrafeeHelper;

    /** @var array  */
    protected $components = [
        Fee::FRONTEND_TYPE_DROPDOWN => 'Amasty_Extrafee/js/fee/item/dropdown',
        Fee::FRONTEND_TYPE_CHECKBOX => 'Amasty_Extrafee/js/fee/item/checkbox',
        Fee::FRONTEND_TYPE_RADIO => 'Amasty_Extrafee/js/fee/item'
    ];

    /**
     * @param FeesInformationManagement $feesInformationManagement
     * @param CheckoutSession $checkoutSession
     * @param ExtrafeeHelper $extrafeeHelper
     */
    public function __construct(
        FeesInformationManagement $feesInformationManagement,
        CheckoutSession $checkoutSession,
        ExtrafeeHelper $extrafeeHelper
    ){
        $this->feesInformationManagement = $feesInformationManagement;
        $this->checkoutSession = $checkoutSession;
        $this->extrafeeHelper = $extrafeeHelper;
    }

    /**
     * Process js Layout of block
     * workarond solution for preload necessary options
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->extrafeeHelper->getScopeValue('frontend/cart') === '1') {
            $quote = $this->checkoutSession->getQuote();
            $fees = $this->feesInformationManagement->collectQuote($quote);

            if (isset($jsLayout['components']['block-amasty-extrafee-summary']['children']['block-amasty-extrafee']['children']
                ['amasty-extrafee-fieldsets']['children'])
            ) {
                $pointer = &$jsLayout['components']['block-amasty-extrafee-summary']['children']['block-amasty-extrafee']
                ['children']['amasty-extrafee-fieldsets']['children'];

                $this->prepareFeesOptions($fees, $pointer);
            }
        }

        if ($this->extrafeeHelper->getScopeValue('frontend/checkout') === '1') {
            $quote = $this->checkoutSession->getQuote();
            $fees = $this->feesInformationManagement->collectQuote($quote);
            
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['block-amasty-extrafee-summary']['children']['block-amasty-extrafee']['children']
            ['amasty-extrafee-fieldsets']['children'])){

                $pointer = &$jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['block-amasty-extrafee-summary']['children']['block-amasty-extrafee']['children']
                ['amasty-extrafee-fieldsets']['children'];

                $this->prepareFeesOptions($fees, $pointer);
            }
        }

        return $jsLayout;
    }

    /**
     * @param array $fees
     * @param array $elements
     */
    protected function prepareFeesOptions(array $fees, array &$elements)
    {
        $quote = $this->checkoutSession->getQuote();

        $items = $this->feesInformationManagement->collectQuote($quote);

        foreach($items as $feeData)
        {
            $id = array_key_exists('id', $feeData) ? $feeData['id'] : null;
            $name = array_key_exists('name', $feeData) ? $feeData['name'] : null;
            $description = array_key_exists('description', $feeData) ? $feeData['description'] : null;
            $options = array_key_exists('base_options', $feeData) ? $feeData['base_options'] : [];
            $frontendType = array_key_exists('frontend_type', $feeData) ? $feeData['frontend_type'] : null;
            $currentValue = array_key_exists('current_value', $feeData) ? $feeData['current_value'] : null;

            if (array_key_exists($frontendType, $this->components)) {
                $elements['fee.' . $id] = [
                    'parent' => '${ $.name }',
                    'name' => '${ $.name }.fee.' . $id,
                    'description' => $description,
                    'component' => $this->components[$frontendType],
                    'options' => $options,
                    'label' => $name,
                    'frontendType' => $frontendType,
                    'feeId' => $id,
                    'currentValue' => $currentValue
                ];
            }
        }
    }
}