<?php
namespace MageArray\OrderComment\Model\Checkout;

class PaymentInformationManagementPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        $extAttributes = $paymentMethod->getExtensionAttributes();
        $comment = $extAttributes->getOrderComment();
        $quote = $this->quoteRepository->getActive($cartId);
        if (isset($comment)) {
            $quote->setOrderComment($comment);
        }

    }

}