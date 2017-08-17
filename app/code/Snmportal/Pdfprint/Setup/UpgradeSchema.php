<?php
namespace Snmportal\Pdfprint\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $connection = $setup->getConnection();
            $setup->getConnection()->addColumn(
                $setup->getTable('snm_pdfprint_template'),
                'type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Template Type'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $connection = $setup->getConnection();
            $setup->getConnection()->addColumn(
                $setup->getTable('snm_pdfprint_template'),
                'is_default',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Template Default'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection = $setup->getConnection();
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                    'LENGTH' => '4G'
                ]
            );
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content2',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'LENGTH' => '4G'
                ]
            );
            $connection->modifyColumn(
                $setup->getTable('snm_pdfprint_template'),
                'content3',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'LENGTH' => '4G'
                ]
            );
        }
        $setup->endSetup();
    }
}
