<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Model;

/**
 * Class TotalsInformationManagement
 *
 * @author Artem Brunevski
 */

use Amasty\Extrafee\Api\TotalsInformationManagementInterface;
use Amasty\Extrafee\Api\Data\TotalsInformationInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Amasty\Extrafee\Model\Quote;
use Amasty\Extrafee\Helper\Data as ExtrafeeHelper;
use Amasty\Extrafee\Api\FeeRepositoryInterface;
use Amasty\Extrafee\Model\QuoteFactory as FeeQuoteFactory;
use Amasty\Extrafee\Model\ResourceModel\Quote\CollectionFactory as FeeQuoteCollectionFactory;
use Amasty\Extrafee\Model\ResourceModel\Fee\CollectionFactory as FeeCollectionFactory;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Checkout\Model\TotalsInformationManagement as CheckoutTotalsInformationManagement;

class TotalsInformationManagement implements TotalsInformationManagementInterface
{
    /** @var CartTotalRepositoryInterface  */
    protected $cartTotalRepository;

    /** @var CartRepositoryInterface  */
    protected $cartRepository;

    /** @var FeeQuoteFactory  */
    protected $feeQuoteFactory;

    /** @var FeeRepositoryInterface  */
    protected $feeRepository;

    /** @var ExtrafeeHelper  */
    protected $extrafeeHelper;

    /** @var FeeQuoteCollectionFactory  */
    protected $feeQuoteCollectionFactory;

    /** @var ObjectConverter  */
    protected $objectConverter;

    /** @var FeeCollectionFactory  */
    protected $feeCollectionFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param FeeRepositoryInterface $feeRepository
     * @param QuoteFactory $feeQuoteFactory
     * @param ExtrafeeHelper $extrafeeHelper
     * @param FeeQuoteCollectionFactory $feeQuoteCollectionFactory
     * @param ObjectConverter $objectConverter
     * @param CheckoutTotalsInformationManagement $checkoutTotalsInformationManagement
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        FeeRepositoryInterface $feeRepository,
        FeeQuoteFactory $feeQuoteFactory,
        ExtrafeeHelper $extrafeeHelper,
        FeeQuoteCollectionFactory $feeQuoteCollectionFactory,
        FeeCollectionFactory $feeCollectionFactory,
        ObjectConverter $objectConverter,
        CheckoutTotalsInformationManagement $checkoutTotalsInformationManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->feeRepository = $feeRepository;
        $this->feeQuoteFactory = $feeQuoteFactory;
        $this->extrafeeHelper = $extrafeeHelper;
        $this->feeQuoteCollectionFactory = $feeQuoteCollectionFactory;
        $this->objectConverter = $objectConverter;
        $this->checkoutTotalsInformationManagement = $checkoutTotalsInformationManagement;
        $this->feeCollectionFactory = $feeCollectionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Framework\DataObject[]
     */
    protected function getQuoteFeesItems(\Magento\Quote\Model\Quote $quote)
    {
        return $this->feeQuoteCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quote->getId())->getItems();
    }

    /**
     * @param int $cartId
     * @param TotalsInformationInterface $information
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        TotalsInformationInterface $information,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ){
        /** @var \Magento\Quote\Model\Quote $quote */

        $quote = $this->cartRepository->get($cartId);

        $optionsIds = $information->getOptionsIds();
        $feeId = $information->getFeeId();

        if (is_array($optionsIds)){

            $fee = $this->feeRepository->getById($feeId);

            //only checkbox type allow multifee mode
            if ($fee->getFrontendType() !== Fee::FRONTEND_TYPE_CHECKBOX){
                $optionsIds = array_slice($optionsIds, 0, 1);
            }

            $feesOptionsHash = $this->objectConverter->toOptionHash(
                $this->getQuoteFeesItems($quote),
                'option_id',
                'entity_id'
            );

            /**
             * fees amount and label will set up on collect totals process
             */
            foreach($optionsIds as $optionId){
                //check that fee wasn't applied
                if ($optionId !== null && !array_key_exists($optionId, $feesOptionsHash)) {
                    $this->feeQuoteFactory->create()
                        ->addData([
                            'quote_id' => $quote->getId(),
                            'fee_id' => $fee->getId(),
                            'option_id' => $optionId,
                        ])->save();
                }
            }

            $this->removeUnselectedOptions(
                $quote->getId(),
                $fee->getId(),
                $optionsIds
            );
        }

        return $this->checkoutTotalsInformationManagement->calculate($cartId, $addressInformation);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    public function updateQuoteFees(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ){
        $feesQuoteCollection = $this->feeQuoteCollectionFactory->create()
            ->addFieldToFilter('option_id', ['neq' => '0'])
            ->addFieldToFilter('quote_id', $quote->getId());

        $feesIds= $this->objectConverter->toOptionHash(
            $this->getQuoteFeesItems($quote),
            'option_id',
            'fee_id'
        );

        $feesItems = $this->feeCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => array_unique($feesIds)])
            ->getItems();

        foreach($feesQuoteCollection as $feesQuoteItem){
            /*
             * custom : add condition to prevent empty subtotal
             */
            if (array_key_exists($feesQuoteItem->getFeeId(), $feesItems) && $total->getSubtotal()) {
                $fee = $feesItems[$feesQuoteItem->getFeeId()];

                $baseOptions = $fee->loadOptions()
                                   ->fetchBaseOptions($quote, $total);

                $option = $this->findOption($baseOptions, $feesQuoteItem->getOptionId());
                /**
                 * if data changed update storage
                 */
                if ($option['price'] !== $feesQuoteItem->getFeeAmount() ||
                    $option['base_price'] !== $feesQuoteItem->getBaseFeeAmount() ||
                    $option['label'] !== $feesQuoteItem->getLabel()
                ) {
                    $feesQuoteItem
                        ->setFeeAmount($option['price'])
                        ->setBaseFeeAmount($option['base_price'])
                        ->setLabel($option['label'])
                        ->save();
                }
            }
        }
    }

    /**
     * @param $quoteId
     * @param $feeId
     * @param array $optionsIds
     */
    protected function removeUnselectedOptions($quoteId, $feeId, array $optionsIds)
    {
        $collection = $this->feeQuoteCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('option_id', ['neq' => '0'])
            ->addFieldToFilter('fee_id', $feeId);

        if (count($optionsIds) > 0){
            $collection->addFieldToFilter('option_id', ['nin' => $optionsIds]);
        }

        foreach($collection as $feeQuoteOption){
            $feeQuoteOption->delete();
        }
    }

    /**
     * @param array $options
     * @param $optionId
     * @return null
     */
    protected function findOption(array $options, $optionId)
    {
        $option = null;

        foreach($options as $item){
            if ((int)$item['index'] === (int)$optionId){
                $option = $item;
                break;
            }
        }

        return $option;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Fee $fee
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote, Fee $fee)
    {
        if ($quote->getItemsCount() === 0 && $this->extrafeeHelper->validateAddress($quote, $fee)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Totals calculation is not applicable to empty cart')
            );
        }
    }
}