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
namespace Bss\DeleteOrder\Plugin\Shipment;

class PluginAfter 
{
    
    public function afterGetBackUrl(\Magento\Shipping\Block\Adminhtml\View $subject){
        $params = $subject->getRequest()->getParams();
        $message = __('Are you sure you want to do this?');
        $subject->addButton(
                'bss-delete',
                ['label' => __('Delete'), 'onclick' => 'confirmSetLocation(\'' . $message . '\',\'' . $this->getDeleteUrl($params['shipment_id']) . '\')', 'class' => 'bss-delete'],
                -1
            );

        return null;
    }

    public function getDeleteUrl($shipmentId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlManager = $objectManager->create('\Magento\Backend\Helper\Data');
        return $urlManager->getUrl(
            'deleteorder/delete/shipment',
            [
                'shipment_id' => $shipmentId
            ]
        );
    }
}
