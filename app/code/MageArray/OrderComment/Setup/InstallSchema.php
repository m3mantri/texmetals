<?php

namespace MageArray\OrderComment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        $eavTable = $installer->getTable('sales_order');

        $columns = [
            'order_comment' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '',
                'nullable' => false,
                'comment' => 'Order Comment',
            ]
        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($eavTable, $name, $definition);
        }

        $installer->endSetup();
    }
}