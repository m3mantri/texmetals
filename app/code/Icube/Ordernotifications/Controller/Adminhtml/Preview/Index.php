<?php 
/**
 * A Magento 2 module named Icube/Ordernotifications
 * Copyright (C) 2016 Derrick Heesbeen
 * 
 * This file included in Icube/Ordernotifications is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Icube\Ordernotifications\Controller\Adminhtml\Preview;
 
 
class Index extends \Magento\Backend\App\Action {

	protected $resultPageFactory;

	protected $_ordernotifications;	
	
	public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Icube\Ordernotifications\Model\OrdernotificationsFactory $ordernotifications
	){
		
		$this->resultPageFactory = $resultPageFactory;
		
		$this->_ordernotifications = $ordernotifications;
		
		parent::__construct($context);
	}

	
	public function execute(){
		
		$id = $this->getRequest()->getParam('ordernotifications_id');
        if($id){
            
            $model = $this->_ordernotifications->create();
            $email = $model->load($id);
                        
            echo $email->getBody();
		}
	}
}
