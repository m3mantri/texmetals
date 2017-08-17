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

class Creditmemo extends \Magento\Backend\App\Action
{
	public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$creditmemoId = $this->getRequest()->getParam('creditmemo_id');
    	$creditmemo = $objectManager->create('Magento\Sales\Api\CreditmemoRepositoryInterface')->get($creditmemoId);
        try {
			$order = $objectManager->create('Bss\DeleteOrder\Model\Creditmemo\Delete')->deleteCreditmemo($creditmemoId);
			$this->messageManager->addSuccess(__('Successfully deleted credit memo #%1.', $creditmemo->getIncrementId()));
		}catch(Exception $e) {
			$this->messageManager->addError(__('Error delete credit memo #%1.', $creditmemo->getIncrementId()));
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('sales/creditmemo/');
		return $resultRedirect;
    }
}
