<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class GuestFeesInformationManagement
 *
 * @author Artem Brunevski
 */

use Amasty\Extrafee\Api\GuestFeesInformationManagementInterface;
use Amasty\Extrafee\Api\FeesInformationManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestFeesInformationManagement implements GuestFeesInformationManagementInterface
{
    /** @var QuoteIdMaskFactory */
    protected $quoteIdMaskFactory;

    /** @var  FeesInformationManagementInterface */
    protected $feesInformationManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param FeesInformationManagementInterface $feesInformationManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        FeesInformationManagementInterface $feesInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->feesInformationManagement = $feesInformationManagement;
    }

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
    ){
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->feesInformationManagement->collect(
            $quoteIdMask->getQuoteId(),
            $paymentMethod,
            $addressInformation
        );
    }
}