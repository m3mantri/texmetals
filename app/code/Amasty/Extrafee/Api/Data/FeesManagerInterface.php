<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api\Data;

/**
 * interface FeesManagerInterface
 *
 * @author Artem Brunevski
 */

interface FeesManagerInterface
{
    const TOTALS = 'totals';
    const FEES = 'fee';

    /**
     * @param \Amasty\Extrafee\Api\Data\FeeInterface[] $fees
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function setFees($fees);

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function setTotals($totals);

    /**
     * @return \Amasty\Extrafee\Api\Data\FeeInterface[]
     */
    public function getFees();

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();
}