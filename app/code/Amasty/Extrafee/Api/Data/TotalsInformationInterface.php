<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api\Data;

/**
 * Interface TotalsInformationInterface
 * @author Artem Brunevski
 * @api
 */
interface TotalsInformationInterface
{
    const OPTIONS_IDS = 'options_ids';
    const FEE_ID = 'fee_id';

    /**
     * @return mixed
     */
    public function getOptionsIds();

    /**
     * @param mixed $feeOptionId
     * @return mixed
     */
    public function setOptionsIds($optionIds);

    /** @return int */
    public function getFeeId();

    /**
     * @param int $feeId
     * @return mixed
     */
    public function setFeeId($feeId);
}