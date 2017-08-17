<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\ResourceModel;

/**
 * Class Quote
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Quote extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_extrafee_quote', 'entity_id');
    }
}