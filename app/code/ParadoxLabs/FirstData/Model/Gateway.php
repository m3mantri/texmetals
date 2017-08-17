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
use Magento\Framework\Exception\PaymentException;

/**
 * First Data API Gateway - custom built for perfection.
 */
class Gateway extends \ParadoxLabs\TokenBase\Model\AbstractGateway
{
    /**
     * @var string
     */
    protected $code = 'paradoxlabs_firstdata';

    /**
     * @var string
     */
    protected $endpointLive = 'https://api.payeezy.com/v1';

    /**
     * @var string
     */
    protected $endpointTest = 'https://api-cert.payeezy.com/v1';

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var string
     */
    protected $jsSecurityKey;

    /**
     * @var string
     */
    protected $transarmorToken;

    /**
     * $fields defines validation for each API parameter or input.
     *
     * key => [
     *    'maxLength' => int,
     *    'noSymbols' => true|false,
     *    'charMask'  => (allowed characters in regex form),
     *    'enum'      => [ values ]
     * ]
     *
     * @var array
     */
    protected $fields = [
        //Tokenize Credit Card Fields
        'apikey'                            => [],
        'js_security_key'                   => [],
        'ta_token'                          => [],
        'auth'                              => ['enum' => ['true', 'false']],
        'credit_card_type'                  => ['enum' => ['visa', 'mastercard', 'amex', 'diners', 'discover', 'jcb']],
        'cardholder_name'                   => ['maxLength' => 100, 'noSymbols' => true],
        'card_number'                       => ['maxLength' => 16, 'charMask' => 'X\d'],
        'exp_date'                          => ['maxLength' => 4, 'charMask' => '\d'],
        'cvv'                               => ['maxLength' => 4, 'charMask' => '\d'],
        'billing_address_city'              => ['maxLength' => 40, 'noSymbols' => true],
        'billing_address_country'           => ['maxLength' => 2, 'noSymbols' => true],
        'billing_address_email'             => ['maxLength' => 255],
        'billing_address_street'            => ['maxLength' => 60, 'noSymbols' => true],
        'billing_address_phone_number'      => ['maxLength' => 25, 'charMask' => '\d\(\)\-\.'],
        'billing_address_state_province'    => ['maxLength' => 2, 'noSymbols' => true],
        'billing_address_zip_postal_code'   => ['maxLength' => 20, 'noSymbols' => true],
        //Token Based Payment Fields
        'token'                             => [],
        'method'                            => ['enum' => ['token']],
        'transaction_type'                  => [
            'enum' => [
                'purchase',
                'authorize',
                'capture',
                'refund',
                'void',
            ],
        ],
        'amount'                            => [],
        'currency_code'                     => ['maxLength' => 3],
        'token_data_value'                  => [],
        'transId'                           => [],
        'transaction_tag'                   => [],
    ];

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $moduleDir;

    /**
     * Gateway constructor.
     *
     * @param \ParadoxLabs\FirstData\Helper\Data $helper
     * @param \ParadoxLabs\TokenBase\Model\Gateway\Xml $xml
     * @param \ParadoxLabs\TokenBase\Model\Gateway\ResponseFactory $responseFactory
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Module\Dir $moduleDir
     * @param array $data
     */
    public function __construct(
        \ParadoxLabs\FirstData\Helper\Data $helper,
        \ParadoxLabs\TokenBase\Model\Gateway\Xml $xml,
        \ParadoxLabs\TokenBase\Model\Gateway\ResponseFactory $responseFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Module\Dir $moduleDir,
        array $data = []
    ) {
        $this->moduleDir = $moduleDir;

        parent::__construct(
            $helper,
            $xml,
            $responseFactory,
            $httpClientFactory,
            $data
        );
    }

    /**
     * Initialize the gateway. Input is taken as an array for greater flexibility.
     *
     * @param array $parameters
     * @return $this
     */
    public function init(array $parameters)
    {
        $this->currencyCode = $parameters['currency_code'];

        $this->jsSecurityKey = $parameters['js_security_key'];

        $this->transarmorToken = $parameters['ta_token'];

        parent::init($parameters);

        return $this;
    }

    /**
     * Set the API credentials so they go through validation.
     *
     * @return $this
     */
    public function clearParameters()
    {
        parent::clearParameters();

        if (isset($this->defaults['login'], $this->defaults['password'])) {
            $this->setParameter('apikey', $this->defaults['login']);
            $this->setParameter('token', $this->defaults['password']);
        }

        return $this;
    }

    /**
     * Send the given request to First Data and process the results.
     *
     * @param array $params
     * @return string
     * @throws LocalizedException
     * @throws PaymentException
     */
    protected function runPaymentTransaction($params)
    {
        $auth = [
            'apikey'    => $this->getParameter('apikey'),
            'token'     => $this->getParameter('token'),
        ];

        $path = '/transactions';

        /**
         * Check to see if transId is set to add it to endpoint
         * transId is needed for capture/invoice only, refund, and void
         */
        if ($this->hasParameter('transId')) {
            $path .= '/' . substr($this->getParameter('transId'), 0, strcspn($this->getParameter('transId'), '-'));
        }

        $httpClient = $this->getHttpClient($path);

        $paramData = json_encode($params);

        $header = $this->createHeader($auth, $paramData);

        $httpClient->setHeaders($header);

        $this->lastRequest = $paramData;

        try {
            $httpClient->setRawData($paramData, 'application/json');
            $response = $httpClient->request(\Zend_Http_Client::POST);
            $responseBody = $response->getBody();

            $this->lastResponse = $responseBody;

            $this->handleResponse($httpClient, json_decode($paramData, true));
        } catch (\Zend_Http_Exception $e) {
            $this->helper->log(
                $this->code,
                sprintf(
                    "CURL Connection error: %s. %s (%s)\nREQUEST: %s",
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno(),
                    $this->sanitizeLog(json_decode($paramData, true))
                )
            );

            throw new LocalizedException(
                __(sprintf(
                    'First Data Gateway Connection error: %s. %s (%s)',
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno()
                ))
            );
        }

        return $this->lastResponse;
    }

    /**
     * Send the given request to First Data and process the results.
     *
     * @param array $params
     * @param bool $isApiTest
     * @return string
     * @throws LocalizedException
     */
    protected function runTokenizationTransaction($params, $isApiTest = false)
    {
        $params['apikey'] = $this->getParameter('apikey');

        $this->lastRequest = $params;

        $path = '/securitytokens';

        $httpClient = $this->getHttpClient($path);

        try {
            $httpClient->setParameterGet($params);
            $response = $httpClient->request(\Zend_Http_Client::GET);

            /**
             * The response returned is a function, so the results need to be extracted from the function
             */
            if (preg_match("/(undefined)?\((.*)\)/s", $response->getBody(), $regExResults)) {
                $responseBody = end($regExResults);
            } else {
                $responseBody = $response->getBody();
            }

            $this->lastResponse = $responseBody;

            $this->handleResponse($httpClient, $params, $isApiTest);
        } catch (\Zend_Http_Exception $e) {
            $this->helper->log(
                $this->code,
                sprintf(
                    "CURL Connection error: %s. %s (%s)\nREQUEST: %s",
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno(),
                    $this->sanitizeLog($params)
                )
            );

            throw new LocalizedException(
                __(sprintf(
                    'First Data Gateway Connection error: %s. %s (%s)',
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno()
                ))
            );
        }

        return $this->lastResponse;
    }

    /**
     * Gets the HTTP Client to be used for transactions
     *
     * @param string $path
     * @return \Magento\Framework\HTTP\ZendClient
     */
    protected function getHttpClient($path)
    {
        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();

        $clientConfig = [
            'adapter'     => '\Zend_Http_Client_Adapter_Curl',
            'timeout'     => 15,
            'curloptions' => [
                CURLOPT_CAINFO         => $this->moduleDir->getDir('ParadoxLabs_FirstData') . '/firstdata-cert.pem',
                CURLOPT_SSL_VERIFYPEER => false,
            ],
        ];

        if ($this->verifySsl === true) {
            $clientConfig['curloptions'][CURLOPT_SSL_VERIFYPEER] = true;
            $clientConfig['curloptions'][CURLOPT_SSL_VERIFYHOST] = 2;
        }

        $httpClient->setUri($this->endpoint . $path);
        $httpClient->setConfig($clientConfig);

        return $httpClient;
    }

    /**
     * Mask certain values in the response for secure logging purposes.
     *
     * @param $array
     * @return mixed
     */
    protected function sanitizeLog($array)
    {
        $maskAll = ['credit_card.cvv', 'ta_token'];
        $maskFour = ['credit_card.card_number', 'js_security_key', 'apikey'];

        foreach ($maskAll as $val) {
            if (isset($array[$val])) {
                $array[$val] = 'XXX';
            }
        }

        foreach ($maskFour as $val) {
            if (isset($array[$val])) {
                $len = strlen($array[$val]) - 4;

                $array[$val] = substr_replace($array[$val], 'XXXX', 0, $len);
            }
        }

        return json_encode($array);
    }

    /**
     * Turn transaction results and directResponse into a usable object.
     *
     * @param string $transactionResult
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws LocalizedException
     * @throws PaymentException
     */
    protected function interpretTransaction($transactionResult)
    {
        /**
         * Turn response into a consistent data object, as best we can
         */
        if (isset($transactionResult['transaction_id'])) {
            $data = $this->getDataFromResponse($transactionResult);
        } else {
            $this->helper->log(
                $this->code,
                sprintf("First Data Gateway: Transaction failed;\n%s", $this->log)
            );

            throw new PaymentException(
                __('First Data Gateway: Transaction failed; '
                    . 'Please re-enter your payment info and try again.')
            );
        }

        /** @var \ParadoxLabs\TokenBase\Model\Gateway\Response $response */
        $response = $this->responseFactory->create();
        $response->setData($data);

        if ($response->getResponseCode() == 17059) {
            $response->setIsFraud(true);
        }

        return $response;
    }

    /**
     * Tokenize credit card to get token
     *
     * @param bool $isApiTest
     * @return string First Data token
     * @throws PaymentException
     */
    public function tokenizeCreditCard($isApiTest = false)
    {
        $params = [];

        $params = $this->tokenizeCreditCardAddPaymentInfo($params);
        $params = $this->tokenizeCreditCardAddCustomerInfo($params);

        $params['js_security_key'] = $this->jsSecurityKey;

        $params['ta_token'] = $this->transarmorToken;

        /*
         * This should always be set to false
         * With it being false, the token can be used authorize, purchase, and reversals (capture, void, and refund)
         * Setting it to true will only allow the token to be used for authorize
         */
        $params['auth'] = 'false';

        $params['type'] = 'FDToken';

        $result = $this->runTokenizationTransaction($params, $isApiTest);
        $paymentId = null;

        if (isset($result['results']['token']['value'])) {
            $paymentId = $result['results']['token']['value'];
        } else {
            //Throw an error if there no token value returned
            throw new PaymentException(__('No token value returned'));
        }

        return $paymentId;
    }

    /**
     * These should be implemented by the child gateway.
     *
     * @param \ParadoxLabs\TokenBase\Api\Data\CardInterface $card
     * @return $this
     */
    public function setCard(\ParadoxLabs\TokenBase\Api\Data\CardInterface $card)
    {
        $this->setParameter('token_data_value', $card->getPaymentId());
        $cardType = $this->helper->mapCcTypeToFirstData($card->getAdditional('cc_type'));
        $this->setParameter('credit_card_type', $cardType);

        $cardHolderName = $card->getAddress('firstname') . ' ' . $card->getAddress('lastname');
        $this->setParameter('cardholder_name', $cardHolderName);

        $this->setParameter(
            'exp_date',
            sprintf('%02d%02d', $card->getAdditional('cc_exp_month'), substr($card->getAdditional('cc_exp_year'), -2))
        );

        $this->setCardBillingInfo($card);
        parent::setCard($card);

        return $this;
    }

    /**
     * Set the billing information from the card
     *
     * @param \ParadoxLabs\TokenBase\Api\Data\CardInterface $card
     * @return $this
     */
    public function setCardBillingInfo(\ParadoxLabs\TokenBase\Api\Data\CardInterface $card)
    {
        $address = $card->getAddressObject();

        $region  = $address->getRegion()->getRegionCode() ?: $address->getRegion()->getRegion();

        $this->setParameter('billing_address_street', implode(', ', $address->getStreet()));
        $this->setParameter('billing_address_city', $address->getCity());
        $this->setParameter('billing_address_state_province', $region);
        $this->setParameter('billing_address_zip_postal_code', $address->getPostcode());
        $this->setParameter('billing_address_country', $address->getCountryId());
        $this->setParameter('billing_address_phone_number', $address->getTelephone());
        $this->setParameter('billing_address_email', $card->getCustomerEmail());

        return $this;
    }

    /**
     * Run an auth transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->setParameter('transaction_type', 'authorize');
        $this->setParameter('amount', $amount);

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    /**
     * Run a capture transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        if ($this->getHaveAuthorized()) {
            $this->setParameter('transaction_type', 'capture');

            if ($transactionId !== null) {
                $this->setParameter('transId', $transactionId);
            } elseif ($payment->getData('transaction_id') != '') {
                $this->setParameter('transId', $payment->getData('transaction_id'));
            }

            if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
                $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
            }
        } else {
            $this->setParameter('transaction_type', 'purchase');
        }

        $this->setParameter('amount', $amount);

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        /**
         * Check for and handle 'transaction not found' error (expired authorization).
         */
        if ($response->getResponseReasonCode() == 16 && $this->getParameter('transId') != '') {
            $this->helper->log(
                $this->code,
                sprintf("Transaction not found. Attempting to recapture.\n%s", json_encode($response->getData()))
            );

            $this->clearParameters()
                ->setCard($this->getData('card'));

            $response = $this->capture($payment, $amount, '');
        }

        return $response;
    }

    /**
     * Run a refund transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        $this->setParameter('transaction_type', 'refund');
        $this->setParameter('amount', $amount);

        if ($transactionId !== null) {
            $this->setParameter('transId', $transactionId);
        } elseif ($payment->getTransactionId() != '') {
            $this->setParameter('transId', $payment->getTransactionId());
        }

        if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
            $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
        }

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        /**
         * Check for 'transaction unsettled' error.
         */
        //TODO : Add code to handle unsettled transactions

        return $response;
    }

    /**
     * Run a void transaction for the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        $this->setParameter('transaction_type', 'void');

        if ($transactionId !== null) {
            $this->setParameter('transId', $transactionId);
        } elseif ($payment->getTransactionId() != '') {
            $this->setParameter('transId', $payment->getTransactionId());
        }

        if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
            $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
        }

        $this->setParameter('amount', $payment->getAdditionalInformation('amount'));

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    /**
     * This does not do anything for First Data, but it is needed since it is an abstract method in the parent class
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     */
    public function fraudUpdate(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        $response = $this->responseFactory->create();

        return $response;
    }

    /**
     * Get transaction ID.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getParameter('transId');
    }

    /**
     * Set prior transaction ID for next transaction.
     *
     * @param $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->setParameter('transId', $transactionId);

        return $this;
    }

########################################################################################################################
#### API methods: See the First Data documentation.
#### https://developer.payeezy.com/apis
########################################################################################################################

    /**
     * Run an actual transaction with First Data with stored data.
     *
     * @return string Raw transaction result (XML)
     */
    public function createTransaction()
    {
        $type = $this->getParameter('transaction_type');

        /**
         * Initialize our params array.
         *
         * NOTE: All elements in the XML array are order-sensitive!
         */
        $params = [];

        /**
         * Define the transaction and basics: Amount, txn ID, auth code
         */
        $params = $this->createTransactionAddTransactionInfo($params, $type);

        /**
         * Add payment info.
         */
        $params = $this->createTransactionAddPaymentInfo($params);

        /**
         * Add customer info.
         */
        $params = $this->createTransactionAddCustomerInfo($params);

        return $this->runPaymentTransaction($params);
    }

    /**
     * Turn the transaction response into an array, as best we can.
     *
     * @param array $response
     * @return array
     * @throws PaymentException
     */
    protected function getDataFromResponse($response)
    {
        if (empty($response)) {
            $this->helper->log(
                $this->code,
                sprintf("First Data Gateway: Transaction failed; no response.\n%s", $this->log)
            );

            throw new PaymentException(
                __('First Data Gateway: Transaction failed; no response. '
                    . 'Please re-enter your payment info and try again.')
            );
        }

        /**
         * Turn the array into a keyed object and infer some things.
         */
        $data = [
            'response_code'            => (int)$this->helper->getArrayValue($response, 'gateway_resp_code'),
            'response_subcode'         => '',
            'response_reason_code'     => (int)$this->helper->getArrayValue($response, 'Error/messages/0/code'),
            'response_reason_text'     => $this->helper->getArrayValue($response, 'Error/messages/0/description'),
            'transaction_id'           => $this->helper->getArrayValue($response, 'transaction_id'),
            'reference_transaction_id' => $this->helper->getArrayValue($response, 'transaction_tag'),
            'amount'                   => $this->getParameter('amount'),
            'method'                   => 'CC',
            'transaction_type'         => $this->getParameter('transaction_type'),
            'card_code_response_code'  => $this->helper->getArrayValue($response, 'cvv2'),
            'avs_result_code'          => $this->helper->getArrayValue($response, 'avs'),
            'card_type'                => $this->getParameter('credit_card_type'),
            'payment_id'               => $this->getParameter('token_data_value'),
            'is_fraud'                 => false,
            'is_error'                 => false,
            'correlation_id'           => $this->helper->getArrayValue($response, 'correlation_id'),
            'bank_resp_code'           => $this->helper->getArrayValue($response, 'bank_resp_code'),
            'bank_message'             => $this->helper->getArrayValue($response, 'bank_message'),
            'gateway_resp_code'        => $this->helper->getArrayValue($response, 'gateway_resp_code'),
            'gateway_message'          => $this->helper->getArrayValue($response, 'gateway_message'),
        ];

        return $data;
    }

    /**
     * Add payment info to a createTransaction API request's parameters.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    protected function createTransactionAddTransactionInfo($params, $type)
    {
        $params['transaction_type'] = $type;

        if ($this->hasParameter('amount')) {
            $params['amount'] = static::formatAmount($this->getParameter('amount'));
        }

        if ($this->hasParameter('transaction_tag')) {
            // Amount is expected to be in cents
            $params['transaction_tag'] = $this->getParameter('transaction_tag');
        }

        $params['currency_code'] = $this->currencyCode;

        $params['method'] = 'token';

        return $params;
    }

    /**
     * Add payment info to a createTransaction API request's parameters.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param array $params
     * @return array
     */
    protected function createTransactionAddPaymentInfo($params)
    {
        $params['token'] = [
            'token_type' => 'FDToken',
            'token_data' => [
                'type'     => $this->getParameter('credit_card_type'),
                'value' => $this->getParameter('token_data_value'),
                'cardholder_name' => $this->getParameter('cardholder_name'),
                'exp_date' => $this->getParameter('exp_date'),
            ],
        ];

        return $params;
    }

    /**
     * Add payment info to a tokenizeCreditCard API request's parameters.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param array $params
     * @return array
     */
    protected function tokenizeCreditCardAddPaymentInfo($params)
    {
        $params['credit_card.card_number'] = $this->getParameter('card_number');
        $params['credit_card.exp_date'] = $this->getParameter('exp_date');
        $params['credit_card.cvv'] = $this->getParameter('cvv');
        $params['credit_card.type'] = $this->getParameter('credit_card_type');
        $params['credit_card.cardholder_name'] = $this->getParameter('cardholder_name');

        return $params;
    }

    /**
     * Add item info to a createTransaction API request's parameters.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param array $params
     * @return array
     */
    protected function createTransactionAddCustomerInfo($params)
    {
        $params['billing_address'] = [
            'street' => $this->getParameter('billing_address_street'),
            'city' => $this->getParameter('billing_address_city'),
            'state_province' => $this->getParameter('billing_address_state_province'),
            'zip_postal_code' => $this->getParameter('billing_address_zip_postal_code'),
            'country' => $this->getParameter('billing_address_country'),
            'email' => $this->getParameter('billing_address_email'),
            'phone' => [
                'number'     => $this->getParameter('billing_address_phone_number'),
            ],
        ];

        return $params;
    }

    /**
     * Add item info to a tokenizeCreditCard API request's parameters.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param array $params
     * @return array
     */
    protected function tokenizeCreditCardAddCustomerInfo($params)
    {
        $params['billing_address.street'] = $this->getParameter('billing_address_street');
        $params['billing_address.city'] = $this->getParameter('billing_address_city');
        $params['billing_address.state_province'] = $this->getParameter('billing_address_state_province');
        $params['billing_address.zip_postal_code'] = $this->getParameter('billing_address_zip_postal_code');
        $params['billing_address.country'] = $this->getParameter('billing_address_country');
        $params['billing_address.phone.number'] = $this->getParameter('billing_address_phone_number');
        $params['billing_address.email'] = $this->getParameter('billing_address_email');

        return $params;
    }

    /**
     * After running a transaction, handle the response.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param \Magento\Framework\HTTP\ZendClient $httpClient
     * @param array $params
     * @param bool $isApiTest
     * @throws LocalizedException
     */
    protected function handleResponse($httpClient, $params, $isApiTest = false)
    {
        if (!empty($this->lastResponse)) {
            $this->lastResponse = json_decode($this->lastResponse, true);

            $this->log .= 'REQUEST: ' . $this->sanitizeLog($params) . "\n";
            $this->log .= 'RESPONSE: ' . $this->sanitizeLog($this->lastResponse) . "\n";

            if ($this->testMode === true && !$isApiTest) {
                $this->helper->log($this->code, $this->log, true);
            }

            /**
             * Check for basic errors.
             */
            $this->handleErrors($isApiTest);
        } else {
            $this->helper->log(
                $this->code,
                sprintf(
                    "CURL Connection error: %s (%s)\nREQUEST: %s",
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno(),
                    $this->sanitizeLog($params)
                )
            );

            throw new LocalizedException(
                __(sprintf(
                    'First Data Gateway Connection error: %s (%s)',
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno()
                ))
            );
        }
    }

    /**
     * After running a transaction, handle any generic errors in the response.
     *
     * Split out to reduce that method's cyclomatic complexity.
     *
     * @param $isApiTest
     * @return void
     * @throws PaymentException
     */
    protected function handleErrors($isApiTest)
    {
        /**
         * Check to see if transaction is payment or credit card tokenization to determine how to handle the errors
         */
        if (isset($this->lastResponse['transaction_status'])
            && $this->lastResponse['transaction_status'] !== 'approved') {
            //Handles if multiple errors are thrown
            $errors = $this->helper->getArrayValue($this->lastResponse, 'Error/messages');

            // Makes sures there are errors
            if (!empty($errors)) {
                $errorMsgArray = [];

                foreach ($errors as $error) {
                    $errorCode = $error['code'];
                    $errorText = $error['description'];
                    /*
                     * Creates string to be used in log
                     * This allows all the errors to be logged in one line instead of one line per error
                     */
                    $errorMsg = sprintf('%s (%s)', $errorText, $errorCode);
                    $errorMsgArray[] = $errorMsg;
                }

                if (!$isApiTest) {
                    $this->helper->log(
                        $this->code,
                        sprintf("API error: %s\n%s", implode(',', $errorMsgArray), $this->log)
                    );
                }

                //Returns a comma-delimited string of all the errors
                throw new PaymentException(__(sprintf('First Data Gateway: %s', implode(',', $errorMsgArray))));
            } elseif (isset($this->lastResponse['gateway_message'])) {
                // Else check if it is a Gateway error
                $errorCode = $this->lastResponse['gateway_resp_code'];
                $errorText = $this->lastResponse['gateway_message'];

                if (!$isApiTest) {
                    $this->helper->log(
                        $this->code,
                        sprintf("API error: %s (%s)\n%s", $errorText, $errorCode, $this->log)
                    );
                }

                throw new PaymentException(__(sprintf('First Data Gateway: %s', $errorText)));
            }
        } else {
            //Handles if multiple errors are thrown
            $errors = $this->helper->getArrayValue($this->lastResponse, 'results/Error/messages');

            // Makes sures there are errors
            if (!empty($errors)) {
                $errorMsgArray = [];

                foreach ($errors as $error) {
                    $errorCode = $error['code'];
                    $errorText = $error['description'];
                    /*
                     * Creates string to be used in log
                     * This allows all the errors to be logged in one line instead of one line per error
                     */
                    $errorMsg = sprintf('%s (%s)', $errorText, $errorCode);
                    $errorMsgArray[] = $errorMsg;
                }

                if (!$isApiTest) {
                    $this->helper->log(
                        $this->code,
                        sprintf("API error: %s\n%s", implode(',', $errorMsgArray), $this->log)
                    );
                }

                //Returns a comma-delimited string of all the errors
                throw new PaymentException(__(sprintf('First Data Gateway: %s', implode(',', $errorMsgArray))));
            }
        }
    }

    /**
     * Create the header for the transactions
     *
     * @param array $auth
     * @param array $paymentData
     * @return array
     */
    protected function createHeader($auth, $paymentData)
    {
        $apiKey = $auth['apikey'];
        $token = $auth['token'];
        $apiSecret = $this->secretKey;

        $nonce = (string)hexdec(bin2hex(openssl_random_pseudo_bytes(4)));
        $timestamp = (string)round(microtime(true) * 1000); //time stamp in milli seconds

        $data = $apiKey . $nonce . $timestamp . $token . $paymentData;

        $hashAlgorithm = 'sha256';
        // Make sure the HMAC hash is in hex
        $hmac = hash_hmac($hashAlgorithm, $data, $apiSecret, false);

        // Authorization : base64 of hmac hash
        $hmac_enc = base64_encode($hmac);

        $header = [
            'Content-Type: application/json',
            'apikey: '. (string)$apiKey,
            'token: '. (string)$token,
            'Authorization: '. $hmac_enc,
            'nonce: '. $nonce,
            'timestamp: '. $timestamp,
        ];

        return $header;
    }

    /**
     * Format amount to the appropriate precision.
     *
     * @param float $amount
     * @return int
     */
    public static function formatAmount($amount)
    {
        // Amount is expected to be in cents
        return round($amount * 100);
    }
}
