<?php 
/**
 * A Magento 2 module named Icube/Ordernotifications
 * 
 * This file included in Icube/Ordernotifications is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Icube\Ordernotifications\Controller\Adminhtml\Ordernotifications;
 
 
class Index extends \Magento\Backend\App\Action {

	protected $resultPageFactory;

	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	
	public function execute(){
		
		$result = $this->resultPageFactory->create();
		$result->getConfig()->getTitle()->prepend(__('Email'));
		return $result;
	}
}
