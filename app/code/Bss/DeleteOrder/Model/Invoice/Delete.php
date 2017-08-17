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
namespace Bss\DeleteOrder\Model\Invoice;

use Magento\Framework\App\ResourceConnection;

class Delete
{
	public function __construct(
        ResourceConnection $resource
    )
    {
    	$this->_resource = $resource;
    }

	public function deleteInvoice($invoiceId)
    {
    	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    	$_helper = $objectManager->create('\Bss\DeleteOrder\Helper\Data');

		$invoiceGridTable = $connection->getTableName($_helper->getTableName('sales_invoice_grid'));
		$invoiceTable = $connection->getTableName($_helper->getTableName('sales_invoice'));

    	$invoice = $objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
		$orderId = $invoice->getOrder()->getId();
		$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		$orderItems = $order->getAllItems();
		$invoiceItems = $invoice->getAllItems();

		// revert item in order
		foreach($orderItems as $item){
			foreach($invoiceItems as $invoiceItem){
				if($invoiceItem->getOrderItemId() == $item->getItemId()){
					$item->setQtyInvoiced($item->getQtyInvoiced() - $invoiceItem->getQty());
					$item->setTaxInvoiced($item->getTaxInvoiced() - $invoiceItem->getTaxAmount());
					$item->setBaseTaxInvoiced($item->getBaseTaxInvoiced() - $invoiceItem->getBaseTaxAmount());
					$item->setDiscountTaxCompensationInvoiced(
		            	$item->getDiscountTaxCompensationInvoiced() - $invoiceItem->getDiscountTaxCompensationAmount()
		            );
			        $item->setBaseDiscountTaxCompensationInvoiced(
			            $item->getBaseDiscountTaxCompensationInvoiced() - $invoiceItem->getBaseDiscountTaxCompensationAmount()
			        );

			        $item->setDiscountInvoiced($item->getDiscountInvoiced() - $invoiceItem->getDiscountAmount());
			        $item->setBaseDiscountInvoiced($item->getBaseDiscountInvoiced() - $invoiceItem->getBaseDiscountAmount());

			        $item->setRowInvoiced($item->getRowInvoiced() - $invoiceItem->getRowTotal());
			        $item->setBaseRowInvoiced($item->getBaseRowInvoiced() - $invoiceItem->getBaseRowTotal());
				}
			}				      	
		}

		// revert info in order
		$order->setTotalInvoiced($order->getTotalInvoiced() - $invoice->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() - $invoice->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotalInvoiced() - $invoice->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotalInvoiced() - $invoice->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxInvoiced() - $invoice->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() - $invoice->getBaseTaxAmount());

        $order->setDiscountTaxCompensationInvoiced(
            $order->getDiscountTaxCompensationInvoiced() - $invoice->getDiscountTaxCompensationAmount()
        );
        $order->setBaseDiscountTaxCompensationInvoiced(
            $order->getBaseDiscountTaxCompensationInvoiced() - $invoice->getBaseDiscountTaxCompensationAmount()
        );

        $order->setShippingTaxInvoiced($order->getShippingTaxInvoiced() - $invoice->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxInvoiced() - $invoice->getBaseShippingTaxAmount());

        $order->setShippingInvoiced($order->getShippingInvoiced() - $invoice->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingInvoiced() - $invoice->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountInvoiced() - $invoice->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountInvoiced() - $invoice->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() - $invoice->getBaseCost());

        if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            $order->setTotalPaid($order->getTotalPaid() - $invoice->getGrandTotal());
            $order->setBaseTotalPaid($order->getBaseTotalPaid() - $invoice->getBaseGrandTotal());
        }

        // delete invoice info
        $connection->rawQuery('DELETE FROM `'.$invoiceGridTable.'` WHERE entity_id='.$invoiceId);
		$connection->rawQuery('DELETE FROM `'.$invoiceTable.'` WHERE entity_id='.$invoiceId);
		if($order->hasShipments() || $order->hasInvoices() || $order->hasCreditmemos()){
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