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

use Magento\Framework\Exception\LocalizedException;

/**
 * First Data card model
 */
class Card extends \ParadoxLabs\TokenBase\Model\Card
{
    /**
     * Finalize before saving.
     *
     * return $this
     */
    public function beforeSave()
    {
        // Send only if we have an info instance for payment data
        if ($this->hasData('info_instance') && $this->getData('no_sync') !== true) {
            if ($this->getInfoInstance()->getData('cc_number') != '') {
                $this->sendDataForTokenization();
            }
        }

        parent::beforeSave();

        return $this;
    }

    /**
     * Attempt to create a CIM payment profile
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function sendDataForTokenization()
    {
        $this->helper->log(
            $this->getMethod(),
            sprintf(
                'sendDataForTokenization() (payment_id %s)',
                var_export($this->getPaymentId(), 1)
            )
        );

        $this->getMethodInstance()->setCard($this);

        /** @var \ParadoxLabs\FirstData\Model\Gateway $gateway */
        $gateway = $this->getMethodInstance()->gateway();

        $address = $this->getAddressObject();

        $region  = $address->getRegion()->getRegionCode() ?: $address->getRegion()->getRegion();

        $gateway->setParameter('cardholder_name', $address->getFirstname() . ' ' . $address->getLastname());
        $gateway->setParameter('billing_address_street', implode(', ', $address->getStreet()));
        $gateway->setParameter('billing_address_city', $address->getCity());
        $gateway->setParameter('billing_address_state_province', $region);
        $gateway->setParameter('billing_address_zip_postal_code', $address->getPostcode());
        $gateway->setParameter('billing_address_country', $address->getCountryId());
        $gateway->setParameter('billing_address_phone_number', $address->getTelephone());
        $gateway->setParameter('billing_address_email', $this->getCustomerEmail());

        $this->setPaymentInfoOnCreate($gateway);

        $paymentId = $gateway->tokenizeCreditCard();

        $response = $gateway->getLastResponse();

        if ($response['results']['status'] == 'failed' || empty($paymentId)) {
            //Handles if multiple errors are thrown
            $errors = $this->helper->getArrayValue($response, 'results/Error/messages');
            $errorTextArray = [];
            $errorMsgArray = [];

            foreach ($errors as $error) {
                $errorCode = $error['code'];
                $errorText = $error['description'];

                //Pushes error text to array to be used to display error(s) on frontend
                $errorTextArray[] = $errorText;

                /*
                 * Creates string to be used in log
                 * This allows all the errors to be logged in one line instead of one line per error
                 */
                $errorMsg = sprintf('%s (%s)', $errorText, $errorCode);
                $errorMsgArray[] = $errorMsg;
            }

            $this->helper->log($this->getMethod(), sprintf('API error: %s', implode(',', $errorMsgArray)));
            $gateway->logLogs();

            //Returns a comma-delimited string of all the errors
            throw new LocalizedException(__('First Data Gateway: %1', implode(',', $errorTextArray)));
        }

        if (!empty($paymentId)) {
            /**
             * Prevent data from being updated multiple times in one request.
             */
            $this->setPaymentId($paymentId);
            $this->setData('no_sync', true);
        } else {
            $gateway->logLogs();

            throw new LocalizedException(__('First Data Gateway: Unable to create payment record.'));
        }

        return $this;
    }

    /**
     * On card save, set payment data to the gateway. (Broken out for extensibility)
     *
     * @param \ParadoxLabs\TokenBase\Api\GatewayInterface $gateway
     * @return $this
     */
    protected function setPaymentInfoOnCreate(\ParadoxLabs\TokenBase\Api\GatewayInterface $gateway)
    {
        /** @var \Magento\Sales\Model\Order\Payment $info */
        $info = $this->getInfoInstance();
        $cardType = $this->helper->mapCcTypeToFirstData($info->getData('cc_type'));
        $gateway->setParameter('credit_card_type', $cardType);
        $gateway->setParameter('card_number', $info->getData('cc_number'));
        $gateway->setParameter('cvv', $info->getData('cc_cid'));
        $gateway->setParameter(
            'exp_date',
            sprintf('%02d-%02d', $info->getData('cc_exp_month'), substr($info->getData('cc_exp_year'), -2))
        );

        return $this;
    }
}
