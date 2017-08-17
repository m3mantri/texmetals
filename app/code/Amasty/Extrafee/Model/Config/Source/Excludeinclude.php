<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Model\Config\Source;

/**
 * Class Excludeinclude
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Option\ArrayInterface;

class Excludeinclude implements ArrayInterface
{
    const VAR_EXCLUDE = '0';
    const VAR_INCLUDE = '1';
    const VAR_DEFAULT = '2';

    protected $useDefaultOption = false;

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options =  [self::VAR_EXCLUDE => __('No'), self::VAR_INCLUDE => __('Yes')];

        if ($this->useDefaultOption) {
            $options[self::VAR_DEFAULT] = __('Default');
        }

        return $options;
    }

    /**
    * Options getter
    *
    * @return array
    */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach($arr as $value => $label){
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * @param $useDefaultOption
     * @return $this
     */
    public function setUseDefaultOption($useDefaultOption)
    {
        $this->useDefaultOption = $useDefaultOption;
        return $this;
    }

}