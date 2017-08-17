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

namespace ParadoxLabs\FirstData\Helper;

/**
 * First Data Helper -- response translation maps et al.
 */
class Data extends \ParadoxLabs\TokenBase\Helper\Data
{
    /**
     * @var array
     */
    protected $avsResponses = [
        'B' => 'No address submitted; could not perform AVS check.',
        'E' => 'AVS data invalid',
        'R' => 'AVS unavailable',
        'G' => 'AVS not supported',
        'U' => 'AVS unavailable',
        'S' => 'AVS not supported',
        'N' => 'Street and zipcode do not match.',
        'A' => 'Street matches; zipcode does not.',
        'Z' => '5-digit zip matches; street does not.',
        'W' => '9-digit zip matches; street does not.',
        'Y' => 'Perfect match',
        'X' => 'Perfect match',
        'P' => 'N/A',
        '1' => 'Cardholder name matches',
        '2' => 'Cardholder name, billing address, and postal code match',
        '3' => 'Cardholder name and billing postal code match',
        '4' => 'Cardholder name and billing address match',
        '5' => 'Cardholder name incorrect, billing address and postal code match',
        '6' => 'Cardholder name incorrect, billing postal code matches',
        '7' => 'Cardholder name incorrect, billing address matches',
        '8' => 'Cardholder name, billing address, and postal code are all incorrect',
    ];

    /**
     * @var array
     */
    protected $ccvResponses = [
        'I' => 'Invalid or empty',
        'M' => 'Passed',
        'N' => 'Failed',
        'P' => 'Not processed',
        'S' => 'Not received',
        'U' => 'N/A',
    ];

    /**
     * @var array
     */
    protected $cardTypeMap = [
        'american express' => 'AE',
        'discover'         => 'DI',
        'diners club'      => 'DC',
        'jcb'              => 'JCB',
        'mastercard'       => 'MC',
        'visa'             => 'VI',
    ];

    /**
     * Translate AVS response codes shown on admin order pages.
     *
     * @param string $code
     * @return \Magento\Framework\Phrase|string
     */
    public function translateAvs($code)
    {
        if (isset($this->avsResponses[$code])) {
            return __(sprintf('%s (%s)', $code, $this->avsResponses[$code]));
        }

        return $code;
    }

    /**
     * Translate CCV response codes shown on admin order pages.
     *
     * @param string $code
     * @return \Magento\Framework\Phrase|string
     */
    public function translateCcv($code)
    {
        if (isset($this->ccvResponses[$code])) {
            return __(sprintf('%s (%s)', $code, $this->ccvResponses[$code]));
        }

        return $code;
    }

    /**
     * Map CC Type to Magento's.
     *
     * @param string $type
     * @return string|null
     */
    public function mapCcTypeToMagento($type)
    {
        if (!empty($type) && isset($this->cardTypeMap[$type])) {
            return $this->cardTypeMap[$type];
        }

        return null;
    }

    /**
     * Map CC Type to First Data's.
     *
     * @param string $type
     * @return string|null
     */
    public function mapCcTypeToFirstData($type)
    {
        if (!empty($type) && in_array($type, $this->cardTypeMap)) {
            return array_search($type, $this->cardTypeMap);
        }

        return null;
    }
}
