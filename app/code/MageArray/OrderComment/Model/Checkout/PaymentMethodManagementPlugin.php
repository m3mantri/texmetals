<?php
namespace MageArray\OrderComment\Model\Checkout;

class PaymentMethodManagementPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \\Magento\Quote\Api\PaymentMethodManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     */
    public function beforeSet(
        \Magento\Quote\Api\PaymentMethodManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $extAttributes = $paymentMethod->getExtensionAttributes();
        $comment = $extAttributes->getOrderComment();
        $quote = $this->quoteRepository->getActive($cartId);
        if (isset($comment)) {
            $quote->setOrderComment($comment);
        }

    }

}