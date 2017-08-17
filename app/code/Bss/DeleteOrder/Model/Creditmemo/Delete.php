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
namespace Bss\DeleteOrder\Model\Creditmemo;

use Magento\Framework\App\ResourceConnection;

class Delete
{
	public function __construct(
        ResourceConnection $resource
    )
    {
    	$this->_resource = $resource;
    }

	public function deleteCreditmemo($creditmemoId)
    {
    	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_helper = $objectManager->create('\Bss\DeleteOrder\Helper\Data');

		$creditmemoGridTable = $connection->getTableName($_helper->getTableName('sales_creditmemo_grid'));
		$creditmemoTable = $connection->getTableName($_helper->getTableName('sales_creditmemo'));

    	$creditmemo = $objectManager->create('Magento\Sales\Api\CreditmemoRepositoryInterface')->get($creditmemoId);
		$orderId = $creditmemo->getOrder()->getId();
		$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		$orderItems = $order->getAllItems();
		$creditmemoItems = $creditmemo->getAllItems();

		// revert item in order
		foreach($orderItems as $item){
			foreach($creditmemoItems as $creditmemoItem){
				if($creditmemoItem->getOrderItemId() == $item->getItemId()){
					$item->setQtyRefunded($item->getQtyRefunded() - $creditmemoItem->getQty());
			        $item->setTaxRefunded($item->getTaxRefunded() - $creditmemoItem->getTaxAmount());
			        $item->setBaseTaxRefunded($item->getBaseTaxRefunded() - $creditmemoItem->getBaseTaxAmount());
			        $item->setDiscountTaxCompensationRefunded(
			            $item->getDiscountTaxCompensationRefunded() - $creditmemoItem->getDiscountTaxCompensationAmount()
			        );
			        $item->setBaseDiscountTaxCompensationRefunded(
			            $item->getBaseDiscountTaxCompensationRefunded() - $creditmemoItem->getBaseDiscountTaxCompensationAmount()
			        );
			        $item->setAmountRefunded($item->getAmountRefunded() - $creditmemoItem->getRowTotal());
			        $item->setBaseAmountRefunded($item->getBaseAmountRefunded() - $creditmemoItem->getBaseRowTotal());
			        $item->setDiscountRefunded($item->getDiscountRefunded() - $creditmemoItem->getDiscountAmount());
			        $item->setBaseDiscountRefunded($item->getBaseDiscountRefunded() - $creditmemoItem->getBaseDiscountAmount());
				}
			}				      	
		}

		// revert info in order
		$order->setBaseTotalRefunded($order->getBaseTotalRefunded() - $creditmemo->getBaseGrandTotal());
        $order->setTotalRefunded($order->getTotalRefunded() - $creditmemo->getGrandTotal());

        $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded() - $creditmemo->getBaseSubtotal());
        $order->setSubtotalRefunded($order->getSubtotalRefunded() - $creditmemo->getSubtotal());

        $order->setBaseTaxRefunded($order->getBaseTaxRefunded() - $creditmemo->getBaseTaxAmount());
        $order->setTaxRefunded($order->getTaxRefunded() - $creditmemo->getTaxAmount());
        $order->setBaseDiscountTaxCompensationRefunded(
            $order->getBaseDiscountTaxCompensationRefunded() - $creditmemo->getBaseDiscountTaxCompensationAmount()
        );
        $order->setDiscountTaxCompensationRefunded(
            $order->getDiscountTaxCompensationRefunded() - $creditmemo->getDiscountTaxCompensationAmount()
        );

        $order->setBaseShippingRefunded($order->getBaseShippingRefunded() - $creditmemo->getBaseShippingAmount());
        $order->setShippingRefunded($order->getShippingRefunded() - $creditmemo->getShippingAmount());

        $order->setBaseShippingTaxRefunded(
            $order->getBaseShippingTaxRefunded() - $creditmemo->getBaseShippingTaxAmount()
        );
        $order->setShippingTaxRefunded($order->getShippingTaxRefunded() - $creditmemo->getShippingTaxAmount());

        $order->setAdjustmentPositive($order->getAdjustmentPositive() - $creditmemo->getAdjustmentPositive());
        $order->setBaseAdjustmentPositive(
            $order->getBaseAdjustmentPositive() - $creditmemo->getBaseAdjustmentPositive()
        );

        $order->setAdjustmentNegative($order->getAdjustmentNegative() - $creditmemo->getAdjustmentNegative());
        $order->setBaseAdjustmentNegative(
            $order->getBaseAdjustmentNegative() - $creditmemo->getBaseAdjustmentNegative()
        );

        $order->setDiscountRefunded($order->getDiscountRefunded() - $creditmemo->getDiscountAmount());
        $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded() - $creditmemo->getBaseDiscountAmount());

        if ($creditmemo->getDoTransaction()) {
            $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOnlineRefunded($order->getBaseTotalOnlineRefunded() - $creditmemo->getBaseGrandTotal());
        } else {
            $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOfflineRefunded(
                $order->getBaseTotalOfflineRefunded() - $creditmemo->getBaseGrandTotal()
            );
        }

        // delete creditmemo info
        $connection->rawQuery('DELETE FROM `'.$creditmemoGridTable.'` WHERE entity_id='.$creditmemoId);
		$connection->rawQuery('DELETE FROM `'.$creditmemoTable.'` WHERE entity_id='.$creditmemoId);

		if($order->hasShipments() || $order->hasInvoices() || $order->hasCreditmemos()){
			$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
					->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
					->save();
		}elseif(!$order->canInvoice() && !$order->canShip() && !$order->hasCreditmemos()){
			$order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)
					->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_COMPLETE))
					->save();
		}else{
			$order->setState(\Magento\Sales\Model\Order::STATE_NEW)
					->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW))
					->save();
		}

        return $order;
    }
}