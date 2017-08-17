<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model\ResourceModel\Fee;

/**
 * Class Collection
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Extrafee\Api\Data\FeeInterface;

class Collection extends AbstractCollection
{
    /** @var MetadataPool  */
    protected $_metadataPool;

    /** @var StoreManagerInterface  */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ){
        $this->_storeManager = $storeManager;
        $this->_metadataPool = $metadataPool;
        return parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Extrafee\Model\Fee', 'Amasty\Extrafee\Model\ResourceModel\Fee');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->_metadataPool->getMetadata(FeeInterface::class);
        $this->performAfterLoad(
            'amasty_extrafee_store',
            'amasty_extrafee_entity_store',
            $entityMetadata->getLinkField(),
            'fee_id',
            'store_id'
        );
        $this->performAfterLoad(
            'amasty_extrafee_customer_group',
            'amasty_extrafee_entity_customer_group',
            $entityMetadata->getLinkField(),
            'fee_id',
            'customer_group_id'
        );
        return parent::_afterLoad();
    }

    /**
     * @param $tableName
     * @param $alias
     * @param $linkField
     * @param $fkField
     * @param $targetField
     */
    protected function performAfterLoad($tableName, $alias, $linkField, $fkField, $targetField)
    {
        $linkedIds = $this->getColumnValues($linkField);

        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from([
                $alias => $this->getTable($tableName)
            ])->where($alias . '.' . $fkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);
            if ($result) {
                $data = [];
                foreach ($result as $item) {
                    $data[$item[$fkField]][] = $item[$targetField];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($data[$linkedId])) {
                        continue;
                    }
                    $item->setData($targetField, $data[$linkedId]);
                }
            }
        }
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this|Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        } else if ($field === 'customer_group_id') {
            return $this->addFilter('customer_group_id', $condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }


    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store_id', ['in' => $store], 'public');

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.entity_id = store_table.' . $linkField,
                []
            )->group(
                'main_table.entity_id'
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinGroupRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('customer_group_id')) {
            $this->getSelect()->join(
                ['group_table' => $this->getTable($tableName)],
                'main_table.entity_id = group_table.' . $linkField,
                []
            )->group(
                'main_table.entity_id'
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('amasty_extrafee_store', 'fee_id');
        $this->joinGroupRelationTable('amasty_extrafee_customer_group', 'fee_id');
    }
}