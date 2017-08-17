<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Model;

/**
 * Class FeeConfigProvider
 *
 * @author Artem Brunevski
 */
use Amasty\Extrafee\Helper\Data as ExtrafeeHelper;
use Magento\Checkout\Model\ConfigProviderInterface;

class FeeConfigProvider implements ConfigProviderInterface
{
    /** @var ExtrafeeHelper  */
    protected $extrafeeHelper;

    /**
     * @param ExtrafeeHelper $extrafeeHelper
     */
    public function __construct(
        ExtrafeeHelper $extrafeeHelper
    ){
        $this->extrafeeHelper = $extrafeeHelper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['amasty'] = [
            'extrafee' => [
                'enabledOnCheckout' => $this->extrafeeHelper->getScopeValue('frontend/checkout'),
                'enabledOnCart' => $this->extrafeeHelper->getScopeValue('frontend/cart')
            ]
        ];
        return $config;
    }
}