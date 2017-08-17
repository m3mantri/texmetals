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
namespace Bss\DeleteOrder\Controller\Adminhtml\Delete;

class Shipment extends \Magento\Backend\App\Action
{
	public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$shipmentId = $this->getRequest()->getParam('shipment_id');
    	$shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        try {
			$order = $objectManager->create('Bss\DeleteOrder\Model\Shipment\Delete')->deleteShipment($shipmentId);
			$this->messageManager->addSuccess(__('Successfully deleted shipment #%1.', $shipment->getIncrementId()));
		}catch(Exception $e) {
			$this->messageManager->addError(__('Error delete shipment #%1.', $shipment->getIncrementId()));
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('sales/shipment/');
		return $resultRedirect;
    }
}
