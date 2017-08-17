<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api;

/**
 * Class FeeRepositoryInterface
 *
 * @author Artem Brunevski
 */
use Amasty\Extrafee\Api\Data\FeeInterface;

interface FeeRepositoryInterface
{
    /**
     * @param FeeInterface $fee
     * @param array $options
     * @return FeeInterface
     */
    public function save(FeeInterface $fee, array $options);

    /**
     * @param $feeId
     * @return FeeInterface
     */
    public function getById($feeId);

    /**
     * @param Data\FeeInterface $fee
     * @return bool true on success
     */
    public function delete(FeeInterface $fee);

    /**
     * @param $feeId
     * @return bool true on success
     */
    public function deleteById($feeId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Amasty\Extrafee\Api\Data\FeeSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \Magento\Quote\Model\Quote $quote
    );

    /**
     * @param $optionId
     * @return FeeInterface
     */
    public function getByOptionId($optionId);
}