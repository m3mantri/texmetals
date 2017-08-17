<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Helper;

/**
 * Class Data
 *
 * @author Artem Brunevski
 */

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ){
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeValue($path)
    {
        return $this->scopeConfig->getValue('amasty_extrafee/' . $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}