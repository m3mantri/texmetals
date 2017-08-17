<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\ResourceModel;

/**
 * Class Fee
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;

class Fee extends AbstractDb
{
    /** @var StoreManagerInterface  */
    protected $_storeManager;

    /** @var MetadataPool  */
    protected $_metadataPool;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ){
        $this->_storeManager = $storeManager;
        $this->_metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('amasty_extrafee', 'entity_id');
    }

    /**
     * @param $feeId
     * @return array
     */
    public function lookupStoreIds($feeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('amasty_extrafee_store'),
            'store_id'
        )->where(
            'fee_id = ?',
            (int)$feeId
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param $feeId
     * @return array
     */
    public function lookupCustomerGroupIds($feeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('amasty_extrafee_customer_group'),
            'customer_group_id'
        )->where(
            'fee_id = ?',
            (int)$feeId
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param $feeId
     * @return array
     */
    public function lookupOptions($feeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('amasty_extrafee_option'),
            ['entity_id', 'fee_id', 'price', 'order', 'price_type', 'default', 'admin', 'options_serialized']
        )->order(
            'order'
        )->where(
            'fee_id = ?',
            (int)$feeId
        );

        $options = $connection->fetchAll($select);
        foreach($options as &$option){
            $option['price'] = number_format($option['price'], 2);
            $option['options'] = @unserialize($option['options_serialized']);
            unset($option['options_serialized']);
        }

        return $options;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function saveStores(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('amasty_extrafee_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['fee_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['fee_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function saveCustomerGroups(AbstractModel $object)
    {
        $oldGroups = $this->lookupCustomerGroupIds($object->getId());
        $newGroups = (array)$object->getGroups();
        if (empty($newGroups)) {
            $newGroups = (array)$object->getGroupId();
        }
        $table = $this->getTable('amasty_extrafee_customer_group');
        $insert = array_diff($newGroups, $oldGroups);
        $delete = array_diff($oldGroups, $newGroups);

        if ($delete) {
            $where = ['fee_id = ?' => (int)$object->getId(), 'customer_group_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $groupId) {
                $data[] = ['fee_id' => (int)$object->getId(), 'customer_group_id' => (int)$groupId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param array $options
     * @return $this
     */
    public function saveOptions(AbstractModel $object, array $options)
    {
        $default = $object->getDefault();

        if (!is_array($default))
            $default = [];

        $deleteIds = [];
        $insertData = [];
        if (is_array($options) && array_key_exists('value', $options)){
            foreach($options['value'] as $id => $value) {
                $entityId = strpos($id, 'option') !== false ? null : $id;
                if ($options['delete'][$id] !== '1') {
                    $insertData[] = [
                        'entity_id' => $entityId,
                        'fee_id' => $object->getId(),
                        'price' => $options['price'][$id],
                        'price_type' => $options['price_type'][$id],
                        'order' => $options['order'][$id],
                        'default' => in_array($id, $default),
                        'admin' => $value[0],
                        'options_serialized' => serialize($value),
                    ];
                } else {
                    $deleteIds[] = $entityId;
                }
            }
        }

        $table = $this->getTable('amasty_extrafee_option');

        if (count($insertData) > 0)
        {
            $this->getConnection()->insertOnDuplicate($table, $insertData);
        }

        if (count($deleteIds) > 0)
        {
            $where = ['fee_id = ?' => (int)$object->getId(), 'entity_id IN (?)' => $deleteIds];
            $this->getConnection()->delete($table, $where);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);

            $groups = $this->lookupCustomerGroupIds($object->getId());
            $object->setData('customer_group_id', $groups);

            $this->loadOptions($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function loadOptions(AbstractModel $object)
    {
        $options = $this->lookupOptions($object->getId());
        $object->setData('options', $options);
        return $object;
    }
}