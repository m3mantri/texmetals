<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_DeleteOrder
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\DeleteOrder\Model\Order;

use Magento\Framework\App\ResourceConnection;

class Delete
{
	public function __construct(
        ResourceConnection $resource
    )
    {
    	$this->_resource = $resource;
    }

	public function deleteOrder($orderId)
    {
    	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_helper = $objectManager->create('\Bss\DeleteOrder\Helper\Data');

        $invoiceGridTable = $connection->getTableName($_helper->getTableName('sales_invoice_grid'));
		$shippmentGridTable = $connection->getTableName($_helper->getTableName('sales_shipment_grid'));
		$creditmemoGridTable = $connection->getTableName($_helper->getTableName('sales_creditmemo_grid'));
		
		$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $order->delete();
		$connection->rawQuery('DELETE FROM `'.$invoiceGridTable.'` WHERE order_id='.$orderId);
		$connection->rawQuery('DELETE FROM `'.$shippmentGridTable.'` WHERE order_id='.$orderId);
		$connection->rawQuery('DELETE FROM `'.$creditmemoGridTable.'` WHERE order_id='.$orderId);
    }
}