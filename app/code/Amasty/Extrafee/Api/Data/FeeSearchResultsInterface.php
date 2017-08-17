<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api\Data;

/**
 * Interface PageSearchResultsInterface
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Api\SearchResultsInterface;

interface FeeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\Extrafee\Api\Data\FeeInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\Extrafee\Api\Data\FeeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}