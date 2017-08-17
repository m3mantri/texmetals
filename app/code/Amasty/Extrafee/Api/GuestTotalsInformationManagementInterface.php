<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api;

/**
 * Interface for quote totals calculation
 * @author Artem Brunevski
 * @api
 */
interface GuestTotalsInformationManagementInterface
{
    /**
     * Calculate quote totals based on quote and fee
     *
     * @param string $cartId
     * @param \Amasty\Extrafee\Api\Data\TotalsInformationInterface $information
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        \Amasty\Extrafee\Api\Data\TotalsInformationInterface $information,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    );
}
