<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api;

/**
 * Interface for collect fees
 * @author Artem Brunevski
 * @api
 */

interface GuestFeesInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param string $paymentMethod
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function collect(
        $cartId,
        $paymentMethod,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    );
}