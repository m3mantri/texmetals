<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\ResourceModel\Quote;

/**
 * Class Collection
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
{
    $this->_init('Amasty\Extrafee\Model\Quote', 'Amasty\Extrafee\Model\ResourceModel\Quote');
}
}