<?php
namespace MageArray\OrderComment\Model\Checkout;

use Magento\Quote\Api\CartRepositoryInterface;

class GuestPaymentInformationManagementPlugin
{

    protected $quoteIdMaskFactory;
    protected $cartRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $extAttributes = $paymentMethod->getExtensionAttributes();
        $comment = $extAttributes->getOrderComment();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId,
            'masked_id');
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        if (isset($comment)) {
            $quote->setOrderComment($comment);
        }
    }

}
