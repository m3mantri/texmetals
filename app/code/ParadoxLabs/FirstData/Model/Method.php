<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 *
 * @author      Chad Bender <support@paradoxlabs.com>
 * @license     http://store.paradoxlabs.com/license.html
 */

namespace ParadoxLabs\FirstData\Model;

/**
 * First Data payment method
 */
class Method extends \ParadoxLabs\TokenBase\Model\AbstractMethod
{
    /**
     * Initialize/return the API gateway class.
     *
     * @api
     *
     * @return \ParadoxLabs\TokenBase\Api\GatewayInterface
     */
    public function gateway()
    {
        if ($this->gateway->isInitialized() !== true) {
            $this->gateway->init([
                'login' => $this->getConfigData('login'),
                'password' => $this->getConfigData('trans_key'),
                'secret_key' => $this->getConfigData('api_secret'),
                'test_mode' => $this->getConfigData('test'),
                'verify_ssl' => $this->getConfigData('verify_ssl'),
                'js_security_key' => $this->getConfigData('js_security_key'),
                'ta_token' => $this->getConfigData('ta_token') ?: '123',
                'currency_code' => $this->getConfigData('currency'),
            ]);
        }

        return $this->gateway;
    }

    /**
     * Catch execution after capturing to reauthorize (if incomplete partial capture).
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param \ParadoxLabs\TokenBase\Model\Gateway\Response $response
     * @return void
     */
    protected function afterCapture(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount,
        \ParadoxLabs\TokenBase\Model\Gateway\Response $response
    ) {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        $outstanding = round($payment->getOrder()->getBaseTotalDue() - $amount, 4);

        /**
         * If this is a pre-auth capture for less than the total value of the order,
         * try to reauthorize any remaining balance. So we have it.
         */
        if ($outstanding > 0) {
            $wasTransId   = $payment->getTransactionId();
            $wasParentId  = $payment->getParentTransactionId();
            $authResponse = null;
            $message      = false;

            if ($this->getConfigData('reauthorize_partial_invoice') == 1) {
                try {
                    $this->log(sprintf('afterCapture(): Reauthorizing for %s', $outstanding));

                    $this->gateway()->clearParameters();
                    $this->gateway()->setCard($this->gateway()->getCard());
                    $this->gateway()->setHaveAuthorized(true);

                    $authResponse    = $this->gateway()->authorize($payment, $outstanding);
                } catch (\Exception $e) {
                    // Reauth failed: Take no action
                    $this->log('afterCapture(): Reauthorization not successful. Continuing with original transaction.');
                }

                /**
                 * Even if the auth didn't go through, we need to create a new 'transaction'
                 * so we can still do an online capture for the remainder.
                 */
                if ($authResponse !== null) {
                    $payment->setTransactionId(
                        $this->getValidTransactionId($payment, $authResponse->getTransactionId())
                    );

                    $payment->setTransactionAdditionalInfo(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                        $authResponse->getData()
                    );

                    $message = __(
                        'Reauthorized outstanding amount of %1.',
                        $payment->formatPrice($outstanding)
                    );
                } else {
                    $payment->setTransactionId(
                        $this->getValidTransactionId($payment, $response->getTransactionId() . '-auth')
                    );
                }

                $payment->setData('parent_transaction_id', null);
                $payment->setIsTransactionClosed(0);

                $transaction = $payment->addTransaction(
                    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,
                    $payment->getOrder(),
                    false
                );

                if ($message !== null) {
                    $payment->addTransactionCommentsToOrder($transaction, $message);
                }

                $payment->setTransactionId($wasTransId);
                $payment->setData('parent_transaction_id', $wasParentId);
            }
        }

        parent::afterCapture($payment, $amount, $response);
    }

    /**
     * Store response statuses persistently.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param \ParadoxLabs\TokenBase\Model\Gateway\Response $response
     * @return \Magento\Payment\Model\InfoInterface
     */
    protected function storeTransactionStatuses(
        \Magento\Payment\Model\InfoInterface $payment,
        \ParadoxLabs\TokenBase\Model\Gateway\Response $response
    ) {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        if ($payment->getData('cc_cid_status') == '' && $response->getData('card_code_response_code') != '') {
            $payment->setData('cc_cid_status', $response->getData('card_code_response_code'));
        }

        return $payment;
    }
}
