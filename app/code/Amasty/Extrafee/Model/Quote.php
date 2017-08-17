<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class Quote
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\AbstractModel;

use Magento\Framework\DataObject\IdentityInterface;

class Quote extends AbstractModel implements IdentityInterface
{
    /**
     * Fee cache tag
     */
    const CACHE_TAG = 'amasty_extrafee_quote';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Extrafee\Model\ResourceModel\Quote');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}