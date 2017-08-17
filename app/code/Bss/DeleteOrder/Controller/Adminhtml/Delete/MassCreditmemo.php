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

class MassCreditmemo extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
	protected $orderManagement;
	protected $_memoCollectionFactory;

	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory,
		OrderManagementInterface $orderManagement,
		\Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory
		) {
		parent::__construct($context, $filter);
		$this->collectionFactory = $collectionFactory;
		$this->orderManagement = $orderManagement;
		$this->_memoCollectionFactory = $memoCollectionFactory;
	}

	protected function massAction(AbstractCollection $collection)
	{
		$params = $this->getRequest()->getParams();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $creditmemoCollections = $this->_memoCollectionFactory->create();
		$selected = array();
		if(isset($params['excluded']) && $params['excluded'] == "false"){
        	if($params['namespace'] == 'sales_order_view_creditmemo_grid'){
				$_order = $objectManager->create('Magento\Sales\Model\Order')->load($params['order_id']);
		        foreach ($_order->getCreditmemosCollection() as $_memo) {
		            if($_memo) array_push($selected, $_memo->getId());
		        }
			}else{
	        	foreach ($creditmemoCollections as $creditmemoCollection){
	        		if($creditmemoCollection) 
	        			array_push($selected, $creditmemoCollection->getId());
	        	}
	        }
        }else{
        	$selected = $params['selected'];
        }
        if($selected){
            foreach ($selected as $creditmemoId) {
				$creditmemo = $objectManager->create('Magento\Sales\Api\CreditmemoRepositoryInterface')->get($creditmemoId);
				try {
					$order = $objectManager->create('Bss\DeleteOrder\Model\Creditmemo\Delete')->deleteCreditmemo($creditmemoId);

					$this->messageManager->addSuccess(__('Successfully deleted credit memo #%1.', $creditmemo->getIncrementId()));
				}catch(Exception $e) {
					$this->messageManager->addError(__('Error delete credit memo #%1.', $creditmemo->getIncrementId()));
				}
			}
        }
		$resultRedirect = $this->resultRedirectFactory->create();
		if($params['namespace'] == 'sales_order_view_creditmemo_grid') 
			$resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
		else $resultRedirect->setPath('sales/creditmemo/');
		return $resultRedirect;
	}
}
