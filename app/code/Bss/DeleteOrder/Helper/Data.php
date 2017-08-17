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
namespace Bss\DeleteOrder\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	private $deploymentConfig;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		DeploymentConfig $deploymentConfig
    ) {
    	parent::__construct($context);
		$this->deploymentConfig = $deploymentConfig;
    }

    public function getTableName($name = null)
    {
        if($name == null) return false;
        $tableName = $name;
		$tablePrefix = (string)$this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
        );
        if($tablePrefix) $tableName = $tablePrefix . $name;
        return $tableName;
    }
}
