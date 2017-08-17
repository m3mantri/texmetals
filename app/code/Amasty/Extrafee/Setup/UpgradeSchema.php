<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

/**
 * Class UpgradeSchema
 *
 * @author Artem Brunevski
 */

namespace Amasty\Extrafee\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addCalculationColumns($setup);
        }

        $setup->endSetup();
    }

    protected function addCalculationColumns(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_extrafee');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'discount_in_subtotal',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT,
                'comment' => 'Discount In Subtotal'
            ]
        );

        $connection->addColumn(
            $table,
            'tax_in_subtotal',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT,
                'comment' => 'Tax In Subtotal'
            ]
        );

        $connection->addColumn(
            $table,
            'shipping_in_subtotal',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT,
                'comment' => 'Shipping In Subtotal'
            ]
        );
    }
}