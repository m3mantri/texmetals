<?php

namespace VladimirPopov\AwHdIntegration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('webforms'),
            'awheldesk_create_tickets',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Create tickets'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('webforms'),
            'awhelpdesk_default_department',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Default department'
            ]
        );

        $setup->endSetup();
    }
}