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

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class MassInvoice extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
	protected $orderManagement;
	protected $_invoiceCollectionFactory;

	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory,
		OrderManagementInterface $orderManagement,
		\Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
	) {
		parent::__construct($context, $filter);
		$this->collectionFactory = $collectionFactory;
		$this->orderManagement = $orderManagement;
		$this->_invoiceCollectionFactory = $invoiceCollectionFactory;
	}

	protected function massAction(AbstractCollection $collection)
	{
        $params = $this->getRequest()->getParams();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $invoicesCollections = $this->_invoiceCollectionFactory->create();
        $selected = array();
		if(isset($params['excluded']) && $params['excluded'] == "false"){
			if($params['namespace'] == 'sales_order_view_invoice_grid'){
				$_order = $objectManager->create('Magento\Sales\Model\Order')->load($params['order_id']);
		        foreach ($_order->getInvoiceCollection() as $_invoice) {
		            if($_invoice) array_push($selected, $_invoice->getId());
		        }
			}else{
	        	foreach ($invoicesCollections as $invoicesCollection){
	        		if($invoicesCollection) array_push($selected, $invoicesCollection->getId());
	        	}
	        }
        }else{
        	$selected = $params['selected'];
        }
        if($selected){
            foreach ($selected as $invoiceId) {
				$invoice = $objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
				try {
					$order = $objectManager->create('Bss\DeleteOrder\Model\Invoice\Delete')->deleteInvoice($invoiceId);
					$this->messageManager->addSuccess(__('Successfully deleted invoice #%1.', $invoice->getIncrementId()));
				}catch(Exception $e) {
					$this->messageManager->addError(__('Error delete invoice #%1.', $invoice->getIncrementId()));
				}
			}
        }
        
		$resultRedirect = $this->resultRedirectFactory->create();
		if($params['namespace'] == 'sales_order_view_invoice_grid') 
			$resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
		else $resultRedirect->setPath('sales/invoice/');
		return $resultRedirect;
	}
}
