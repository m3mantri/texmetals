<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class FeeRepository
 *
 * @author Artem Brunevski
 */

use Amasty\Extrafee\Api\Data\FeeSearchResultsInterfaceFactory;
use Amasty\Extrafee\Api\FeeRepositoryInterface;
use Amasty\Extrafee\Api\Data\FeeInterface;
use Amasty\Extrafee\Api\Data\FeeInterfaceFactory;
use Amasty\Extrafee\Model\ResourceModel\Fee as ResourceFee;
use Amasty\Extrafee\Model\ResourceModel\Fee\CollectionFactory as FeeCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Extrafee\Model\FeeFactory;
use Amasty\Extrafee\Model\ResourceModel\Quote\CollectionFactory as FeeQuoteCollectionFactory;
use Amasty\Extrafee\Model\QuoteFactory as QuoteFeeFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Quote\Model\Quote\Address\TotalFactory;
class FeeRepository implements FeeRepositoryInterface
{
    /** @var ResourceFee  */
    protected $_resource;

    /** @var  FeeFactory */
    protected $_feeFactory;

    /** @var FeeSearchResultsInterfaceFactory  */
    protected $_searchResultsFactory;

    /** @var FeeCollectionFactory  */
    protected $_feeCollectionFactory;

    /** @var FeeInterfaceFactory  */
    protected $_feeInterfaceFactory;

    /** @var DataObjectHelper  */
    protected $_dataObjectHelper;

    /** @var DataObjectProcessor  */
    protected $_dataObjectProcessor;

    /** @var RuleFactory  */
    protected $_ruleFactory;

    /** @var FeeQuoteCollectionFactory  */
    protected $_feeQuoteCollectionFactory;

    /** @var QuoteFactory  */
    protected $_quoteFeeFactory;

    /** @var TotalFactory  */
    protected $_totalFactory;

    /**
     * @param ResourceFee $resource
     * @param \Amasty\Extrafee\Model\FeeFactory $feeFactory
     * @param FeeSearchResultsInterfaceFactory $searchResultsFactory
     * @param FeeCollectionFactory $feeCollectionFactory
     * @param FeeInterfaceFactory $feeInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param RuleFactory $ruleFactory
     * @param FeeQuoteCollectionFactory $feeQuoteCollectionFactory
     * @param QuoteFactory $quoteFeeFactory
     */
    public function __construct(
        ResourceFee $resource,
        FeeFactory $feeFactory,
        FeeSearchResultsInterfaceFactory $searchResultsFactory,
        FeeCollectionFactory $feeCollectionFactory,
        FeeInterfaceFactory $feeInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        RuleFactory $ruleFactory,
        FeeQuoteCollectionFactory $feeQuoteCollectionFactory,
        QuoteFeeFactory $quoteFeeFactory,
        TotalFactory $totalFactory
    ){
        $this->_resource = $resource;
        $this->_feeFactory = $feeFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_feeCollectionFactory = $feeCollectionFactory;
        $this->_feeInterfaceFactory = $feeInterfaceFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataObjectProcessor = $dataObjectProcessor;
        $this->_ruleFactory = $ruleFactory;
        $this->_feeQuoteCollectionFactory = $feeQuoteCollectionFactory;
        $this->_quoteFeeFactory = $quoteFeeFactory;
        $this->_totalFactory = $totalFactory;
    }

    /**
     * @param FeeInterface $fee
     * @return FeeInterface
     * @throws CouldNotSaveException
     */
    public function save(FeeInterface $fee, array $options)
    {
        try {
            $this->_resource
                ->save($fee)
                ->saveOptions($fee, $options)
                ->saveStores($fee)
                ->saveCustomerGroups($fee);

        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the fee: %1',
                $exception->getMessage()
            ));
        }
        return $fee;
    }

    /**
     * @return Fee
     */
    public function create()
    {
        return $this->_feeFactory->create();
    }

    /**
     * @param $feeId
     * @return FeeInterface
     * @throws NoSuchEntityException
     */
    public function getById($feeId)
    {
        $fee = $this->create();
        $fee->load($feeId);
        if (!$fee->getId()) {
            throw new NoSuchEntityException(__('Fee with id "%1" does not exist.', $feeId));
        }
        return $fee;
    }

    /**
     * @param FeeInterface $fee
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FeeInterface $fee)
    {
        try {
            $this->_resource->delete($fee);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the fee: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $feeId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($feeId)
    {
        return $this->delete($this->getById($feeId));
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    protected function getShippingAddressTotal(\Magento\Quote\Model\Quote $quote)
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->_totalFactory->create('Magento\Quote\Model\Quote\Address\Total');
        $address = $quote->getShippingAddress();

        $total
            ->addBaseTotalAmount('subtotal', $address->getBaseSubtotal())
            ->addBaseTotalAmount('discount', $address->getBaseDiscountAmount())
            ->addBaseTotalAmount('tax', $address->getBaseTaxAmount())
            ->addBaseTotalAmount('shipping', $address->getBaseShippingAmount());

        return $total;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Amasty\Extrafee\Api\Data\FeeSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria,
        \Magento\Quote\Model\Quote $quote
    ){
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \Amasty\Extrafee\Model\ResourceModel\Fee\Grid\Collection $collection */
        $collection = $this->_feeCollectionFactory->create();

        $searchResults->setTotalCount($collection->getSize());
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $collection->addOrder('sort_order', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);

        foreach ($criteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $matchedFeesIds = [];
        $items = [];
        /** @var Fee $model */
        foreach ($collection as $model) {

            if ($this->validateAddress($quote, $model)) {
                $this->_resource->loadOptions($model);

                $options = $model->getOptions();

                $baseOptions = $model->fetchBaseOptions(
                    $quote,
                    $this->getShippingAddressTotal($quote)
                );

                $this->calculateDefaultFee($quote, $model, $baseOptions);

                $model
                    ->setBaseOptions($baseOptions)
                    ->setCurrentValue($this->getSelectionOptionsIds($quote, $model));

                $itemData = $this->_feeInterfaceFactory->create();

                $this->_dataObjectHelper->populateWithArray(
                    $itemData,
                    $model->getData(),
                    'Amasty\Extrafee\Api\Data\FeeInterface'
                );

                $items[] = $this->_dataObjectProcessor->buildOutputDataArray(
                    $itemData,
                    'Amasty\Extrafee\Api\Data\FeeInterface'
                );

                $matchedFeesIds[] = $model->getId();
            } else {
                $feesQuoteCollection = $this->_feeQuoteCollectionFactory->create()
                    ->addFieldToFilter('fee_id', $model->getId())
                    ->addFieldToFilter('quote_id', $quote->getId());

                $feesQuoteCollection->load();

                foreach($feesQuoteCollection as $feeQuote){
                    $feeQuote->delete();
                }
            }
        }
        $searchResults->setItems($items);
        $this->removeUnmatchedFees($quote, $matchedFeesIds);

        return $searchResults;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Fee $fee
     * @param array $options
     */
    public function calculateDefaultFee(\Magento\Quote\Model\Quote $quote, Fee $fee, array $options)
    {
        $id = $fee->getId();

        $feesQuoteCollection = $this->_feeQuoteCollectionFactory->create()
            ->addFieldToFilter('fee_id', $id)
            ->addFieldToFilter('quote_id', $quote->getId());

        //fee not initialize for quote
        if ($feesQuoteCollection->getSize() === 0){
            //have to initialize by zero value, for know that initialized, when customer unselect all options
            $this->_quoteFeeFactory->create()
                ->addData([
                    'quote_id' => $quote->getId(),
                    'fee_id' => $id,
                    'option_id' => '0',
                    'fee_amount' => '0',
                    'base_fee_amount' => '0',
                    'label' => ''
                ])->save();

            //initialized admin selected option
            foreach($options as $option){
                if ($option['default']){
                    $this->_quoteFeeFactory->create()
                        ->addData([
                            'quote_id' => $quote->getId(),
                            'fee_id' => $id,
                            'option_id' => $option['index'],
                            'label' => $option['label'],
                            'fee_amount' => $option['price'],
                            'base_fee_amount' => $option['base_price'],
                        ])->save();
                    break;
                }
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $matchedFeesIds
     */
    public function removeUnmatchedFees(\Magento\Quote\Model\Quote $quote, array $matchedFeesIds)
    {
        if (count($matchedFeesIds) > 0) {
            $feesQuoteCollection = $this->_feeQuoteCollectionFactory->create()
                ->addFieldToFilter('fee_id', ['nin' => $matchedFeesIds])
                ->addFieldToFilter('quote_id', $quote->getId());

            foreach ($feesQuoteCollection as $feeQuote) {
                $feeQuote->delete();
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Fee $fee
     * @return array
     */
    protected function getSelectionOptionsIds(\Magento\Quote\Model\Quote $quote, Fee $fee)
    {
        $optionsIds = [];

        $feesQuoteCollection = $this->_feeQuoteCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quote->getId())
            ->addFieldToFilter('fee_id', $fee->getId())
            ->addFieldToFilter('option_id', ['neq' => '0']);

        foreach($feesQuoteCollection as $feeOption)
        {
            $optionsIds[] = $feeOption->getOptionId();
        }

        return $fee->getFrontendType() === Fee::FRONTEND_TYPE_CHECKBOX ? $optionsIds : end($optionsIds);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Fee $fee
     * @return bool
     */
    public function validateAddress(
        \Magento\Quote\Model\Quote $quote,
        \Amasty\Extrafee\Model\Fee $fee
    ){
        $valid = false;
        $salesRule = $this->getSalesRule($fee);
        $address = $quote->getShippingAddress();
        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        $address->setData('total_qty', $quote->getData('items_qty'));
        if ($salesRule->validate($address)){
            $valid = true;
        }

        return $valid;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param AbstractCollection $collection
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        AbstractCollection $collection
    ) {
        
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * @param FeeInterface $fee
     * @return \Magento\SalesRule\Model\Rule
     */
    public function getSalesRule(FeeInterface $fee)
    {
        $rule = $this->_ruleFactory->create();
        $rule->setConditionsSerialized($fee->getConditionsSerialized());
        return $rule;
    }

    /**
     * @param $optionId
     * @return FeeInterface
     * @throws NoSuchEntityException
     */
    public function getByOptionId($optionId)
    {
        $connection = $this->_resource->getConnection();

        $tableName = $this->_resource->getTable('amasty_extrafee_option');
        $select = $connection->select()
            ->from($tableName, 'fee_id')
            ->where(
            'entity_id = ?',
            (int)$optionId
        );

        $data = $connection->fetchRow($select);

        return $this->getById($data['fee_id']);
    }
}