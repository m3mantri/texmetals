<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

/**
 * Class Fee
 *
 * @author Artem Brunevski
 */

namespace Amasty\Extrafee\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Amasty\Extrafee\Model\ResourceModel\Quote\CollectionFactory as FeeQuoteCollectionFactory;

class Fee extends AbstractTotal
{
    /**
     * @var FeeQuoteCollectionFactory
     */
    protected $feeQuoteCollectionFactory;

    /**
     * @param FeeQuoteCollectionFactory $feeQuoteCollectionFactory
     */
    public function __construct(
        FeeQuoteCollectionFactory $feeQuoteCollectionFactory
    ){
        $this->feeQuoteCollectionFactory = $feeQuoteCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $feesQuoteCollection = $this->feeQuoteCollectionFactory->create()
            ->addFieldToFilter('option_id', ['neq' => '0'])
            ->addFieldToFilter('quote_id', $order->getQuoteId());

        $feeAmount = 0;
        $baseFeeAmount = 0;

        foreach($feesQuoteCollection as $feeOption) {
            $feeAmount += $feeOption->getFeeAmount();
            $baseFeeAmount += $feeOption->getBaseFeeAmount();
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmount);

        return $this;
    }
}