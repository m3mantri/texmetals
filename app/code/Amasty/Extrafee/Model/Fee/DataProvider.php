<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\Fee;

/**
 * Class DataProvider
 *
 * @author Artem Brunevski
 */

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Extrafee\Model\ResourceModel\Fee\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\DynamicRows;

class DataProvider extends AbstractDataProvider
{
    /** @var \Amasty\Extrafee\Model\ResourceModel\Fee\Collection */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var  array */
    protected $stores;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $feeCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $feeCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        if ($this->stores === null) {
            $this->stores = $this->storeManager->getStores(true);
        }
        return $this->stores;
    }

    /**
     * @return array|mixed
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }
                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }
        return $stores;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        $metaOptions = &$meta['options']['children']['rows']['children']['record']['children'];
        /** @var Store $store */
        foreach($this->getStoresSortedBySortOrder() as $store){
            $validation = [];
            $label = $store->getName();
            $required = false;
            if ($store->getId() == Store::DEFAULT_STORE_ID){
                $label = __($label);
                $required = true;
                $validation['required-entry'] = true;
            }

            $metaOptions['store_' . $store->getId()] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'text',
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'label' => $label,
                            'required' => $required,
                            'validation' => $validation
                        ]
                    ]
                ]
            ];
        }

        $metaOptions['remove'] = [
            'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'actionDelete',
                            'dataType' => 'text',
                            'fit' => true
                        ]
                ]
            ]

        ];

        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $page \Amasty\Extrafee\Model\Fee */
        foreach ($items as $fee) {
            $data = $fee->getData();

            $options = @unserialize($data['options_serialized']);

            if (is_array($options)){
                $data['options'] = $options;
            }
            $this->loadedData[$fee->getId()] = $data;
        }

        return $this->loadedData;
    }
}