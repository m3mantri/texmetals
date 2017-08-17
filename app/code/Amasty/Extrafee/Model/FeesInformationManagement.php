<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class FeesInformationManagement
 *
 * @author Artem Brunevski
 */

use Amasty\Extrafee\Api\FeesInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Amasty\Extrafee\Model\Data\FeesManagerFactory;

use Magento\Checkout\Model\TotalsInformationManagement as CheckoutTotalsInformationManagement;


class FeesInformationManagement implements FeesInformationManagementInterface
{
    /** @var CartRepositoryInterface  */
    protected $cartRepository;

    /** @var FeeRepository  */
    protected $feeRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var FilterBuilder  */
    protected $filterBuilder;

    /** @var FilterGroupBuilder  */
    protected $filterGroupBuilder;

    /** @var SortOrderBuilder  */
    protected $sortOrderBuilder;

    /** @var CheckoutTotalsInformationManagement  */
    protected $checkoutTotalsInformationManagement;

    /** @var FeesManagerFactory  */
    protected $feesManagerFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param FeeRepository $feeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param CheckoutTotalsInformationManagement $checkoutTotalsInformationManagement
     * @param FeesManagerFactory $feesManagerFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        FeeRepository $feeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CheckoutTotalsInformationManagement $checkoutTotalsInformationManagement,
        FeesManagerFactory $feesManagerFactory
    ){
        $this->cartRepository = $cartRepository;
        $this->feeRepository = $feeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->checkoutTotalsInformationManagement = $checkoutTotalsInformationManagement;
        $this->feesManagerFactory = $feesManagerFactory;
    }

    /**
     * @param int $cartId
     * @param string $paymentMethod
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Amasty\Extrafee\Api\Data\FeesManagerInterface
     */
    public function collect(
        $cartId,
        $paymentMethod,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ){
        $quote = $this->cartRepository->get($cartId);
        $quote->getShippingAddress()->setPaymentMethod($paymentMethod);
        $this->checkoutTotalsInformationManagement->calculate($cartId, $addressInformation);

        //getting and validating fees according to current quote
        $fees = $this->collectQuote($quote);

        //recalculate quote totals according to just loaded extra fees
        $quote->setTotalsCollectedFlag(false);
        $totals = $this->checkoutTotalsInformationManagement->calculate($cartId, $addressInformation);

        $feesManager = $this->feesManagerFactory->create()
            ->setFees($fees)
            ->setTotals($totals);

        return $feesManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Amasty\Extrafee\Api\Data\FeeInterface[]
     */
    public function collectQuote(
        \Magento\Quote\Model\Quote $quote
    ){
        $filterEnabled = $this->filterBuilder->setField('enabled')
            ->setValue('1')
            ->setConditionType('eq')
            ->create();

        $filterStore = $this->filterBuilder->setField('store_id')
            ->setValue(['0', $quote->getStoreId()])
            ->setConditionType('in')
            ->create();

        $filterCustomerGroup = $this->filterBuilder->setField('customer_group_id')
            ->setValue($quote->getCustomerGroupId())
            ->setConditionType('eq')
            ->create();

        $filterGroup = $this->filterGroupBuilder
            ->addFilter($filterEnabled)
            ->addFilter($filterStore)
            ->addFilter($filterCustomerGroup)
            ->create();

        $criteria = $this->searchCriteriaBuilder->create()
            ->setFilterGroups([$filterGroup])
            ->setSortOrders(
                $this->sortOrderBuilder->create()
                    ->setField('sort_order')
                    ->getDirection('ASC')
            );

        $searchResults = $this->feeRepository->getList(
            $criteria,
            $quote
        );

        return $searchResults->getItems();
    }
}