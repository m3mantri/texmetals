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
namespace Bss\DeleteOrder\Model\Shipment;

use Magento\Framework\App\ResourceConnection;

class Delete
{
	public function __construct(
        ResourceConnection $resource
    )
    {
    	$this->_resource = $resource;
    }

	public function deleteShipment($shipmentId)
    {
    	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_helper = $objectManager->create('\Bss\DeleteOrder\Helper\Data');

        $shipmentTable = $connection->getTableName($_helper->getTableName('sales_shipment'));
        $shipmentGridTable = $connection->getTableName($_helper->getTableName('sales_shipment_grid'));

        $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        $orderId = $shipment->getOrder()->getId();
		$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		$orderItems = $order->getAllItems();
		$shipmentItems = $shipment->getAllItems();

		// revert item in order
		foreach($orderItems as $item){
			foreach($shipmentItems as $shipmentItem){
				if($shipmentItem->getOrderItemId() == $item->getItemId()){
					$item->setQtyShipped($item->getQtyShipped() - $shipmentItem->getQty());
				}
			}				      	
		}

		// delete shipment info
        $connection->rawQuery('DELETE FROM `'.$shipmentGridTable.'` WHERE entity_id='.$shipmentId);
		$connection->rawQuery('DELETE FROM `'.$shipmentTable.'` WHERE entity_id='.$shipmentId);
		if($order->hasShipments() || $order->hasInvoices()  || $order->hasCreditmemos()){
			$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
					->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
					->save();
		}else{
			$order->setState(\Magento\Sales\Model\Order::STATE_NEW)
					->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW))
					->save();
		}

		return $order;
    }
}