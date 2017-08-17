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

namespace Icube\Ordernotifications\Ui\Component\Listing\Column;
 
 
class OrdernotificationsActions extends \Magento\Ui\Component\Listing\Columns\Column {

	const URL_PATH_EDIT = 'icube_ordernotifications/preview/index';
	const URL_PATH_DELETE = 'icube_ordernotifications/ordernotifications/delete';
	const URL_PATH_DETAILS = 'icube_ordernotifications/ordernotifications/details';
	protected $urlBuilder;

	
	public function __construct(
		\Magento\Framework\View\Element\UiComponent\ContextInterface $context,
		\Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
		\Magento\Framework\UrlInterface $urlBuilder,
		array $components = [],
		array $data = []
	){
		$this->urlBuilder = $urlBuilder;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	
	public function prepareDataSource(array $dataSource){
		if (isset($dataSource['data']['items'])) {
		foreach ($dataSource['data']['items'] as & $item) {
		    if (isset($item['ordernotifications_id'])) {
		        $item[$this->getData('name')] = [
		            'view' => [
		                'href' => $this->urlBuilder->getUrl(
		                    static::URL_PATH_EDIT,
		                    [
		                        'ordernotifications_id' => $item['ordernotifications_id']
		                    ]
		                ),
		                'label' => __('View'),
						'popup' => true
						//'callback' => "window.open(this.href,'_blank','width=800,height=700,resizable=1,scrollbars=1');return false;",
						//'confirm'=> ['title'=>'test','message'=>'test']
		            ]
		        ];
		    }
		}
		}
		
		return $dataSource;
	}
}
