<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class GuestTotalsInformationManagement
 *
 * @author Artem Brunevski
 */

use Amasty\Extrafee\Api\GuestTotalsInformationManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Amasty\Extrafee\Api\TotalsInformationManagementInterface;
use Amasty\Extrafee\Api\Data\TotalsInformationInterface;

class GuestTotalsInformationManagement implements GuestTotalsInformationManagementInterface
{
    /** @var QuoteIdMaskFactory */
    protected $quoteIdMaskFactory;

    /** @var  TotalsInformationManagementInterface */
    protected $totalsInformationManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param TotalsInformationManagementInterface $totalsInformationManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        TotalsInformationManagementInterface $totalsInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->totalsInformationManagement = $totalsInformationManagement;
    }

    /**
     * @param string $cartId
     * @param TotalsInformationInterface $information
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        TotalsInformationInterface $information,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->totalsInformationManagement->calculate(
            $quoteIdMask->getQuoteId(),
            $information,
            $addressInformation
        );
    }
}