<?php 
/**
 * A Magento 2 module named Icube/Ordernotifications
 * Copyright (C) 2016 Derrick Heesbeen
 * 
 * This file included in Icube/Ordernotifications is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Icube\Ordernotifications\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface; 
 
class InstallSchema implements InstallSchemaInterface {


	
	public function install(
		SchemaSetupInterface $setup,
		ModuleContextInterface $context
	){
		$installer = $setup;
		$installer->startSetup();

		$table_icube_ordernotifications = $setup->getConnection()->newTable($setup->getTable('icube_ordernotifications'));

		$table_icube_ordernotifications->addColumn(
		  'ordernotifications_id',
		  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		  null,
		  array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
		  'Entity ID'
		);

		$table_icube_ordernotifications->addColumn(
		  'to',
		  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		  null,
		  [],
		  'to'
		);

		$table_icube_ordernotifications->addColumn(
		  'from',
		  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		  null,
		  [],
		  'from'
		);

		$table_icube_ordernotifications->addColumn(
		  'subject',
		  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		  null,
		  [],
		  'subject'
		);

		$table_icube_ordernotifications->addColumn(
		  'body',
		  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		  null,
		  [],
		  'body'
		);

		$table_icube_ordernotifications->addColumn(
		  'created_at',
		  \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
		  null,
		  [],
		  'created_at'
		);

		$table_icube_ordernotifications->addColumn(
		  'store_id',
		  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		  null,
		  [],
		  'store_id'
		);

		$setup->getConnection()->createTable($table_icube_ordernotifications);

		$setup->endSetup();
	}
}
