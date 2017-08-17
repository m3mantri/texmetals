<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\Fee\Source;

/**
 * Class PriceType
 *
 * @author Artem Brunevski
 */
use Magento\Framework\Data\OptionSourceInterface;
use Amasty\Extrafee\Model\Fee;

class PriceType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Fixed'),
                'value' => Fee::PRICE_TYPE_FIXED
            ],
            [
                'label' => __('Percent'),
                'value' => Fee::PRICE_TYPE_PERCENT
            ]
        ];
    }
}