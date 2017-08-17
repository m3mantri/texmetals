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

class Invoice extends \Magento\Backend\App\Action
{
	public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$invoiceId = $this->getRequest()->getParam('invoice_id');
    	$invoice = $objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
        try {
			$order = $objectManager->create('Bss\DeleteOrder\Model\Invoice\Delete')->deleteInvoice($invoiceId);
			$this->messageManager->addSuccess(__('Successfully deleted invoice #%1.', $invoice->getIncrementId()));
		}catch(Exception $e) {
			$this->messageManager->addError(__('Error delete invoice #%1.', $invoice->getIncrementId()));
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('sales/invoice/');
		return $resultRedirect;
    }
}
