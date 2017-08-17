<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class TotalsInformation
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\AbstractExtensibleModel;
use Amasty\Extrafee\Api\Data\TotalsInformationInterface;

class TotalsInformation extends AbstractExtensibleModel implements TotalsInformationInterface
{
    /**
     * @return mixed
     */
    public function getOptionsIds()
    {
        return $this->getData(self::OPTIONS_IDS);
    }

    /**
     * @param $optionsIds
     * @return $this
     */
    public function setOptionsIds($optionsIds)
    {
        return $this->setData(self::OPTIONS_IDS, $optionsIds);
    }

    /** @return int */
    public function getFeeId()
    {
        return $this->getData(self::FEE_ID);
    }

    /**
     * @param int $feeId
     * @return mixed
     */
    public function setFeeId($feeId)
    {
        return $this->setData(self::FEE_ID, $feeId);
    }
}