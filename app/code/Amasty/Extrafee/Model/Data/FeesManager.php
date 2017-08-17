<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\Data;

/**
 * Class FeesManager
 *
 * @author Artem Brunevski
 */

class FeesManager extends \Magento\Framework\Model\AbstractExtensibleModel implements \Amasty\Extrafee\Api\Data\FeesManagerInterface
{
    /**
     * @param \Amasty\Extrafee\Api\Data\FeeInterface[] $fees
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function setFees($fees)
    {
        return $this->setData(self::FEES, $fees);
    }

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * @return \Amasty\Extrafee\Api\Data\FeeInterface[]
     */
    public function getFees()
    {
        return $this->getData(self::FEES);
    }

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }
}