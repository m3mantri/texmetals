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

namespace Icube\Ordernotifications\Model;
 
 
class Ordernotifications extends \Magento\Framework\Model\AbstractModel {


	
	protected function _construct(){
		$this->_init('Icube\Ordernotifications\Model\ResourceModel\Ordernotifications');
	}
}
