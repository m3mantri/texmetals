<?php
namespace Snmportal\Pdfprint\Model\Pdf\Filter;
class Helper extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Snmportal\Pdfprint\Model\Pdf\Barcode\Barcode
     */
    protected $barcodeGenerator;
    /*
    protected $_allowedFormats = array(
        Mage_Core_Model_Locale::FORMAT_TYPE_FULL,
        Mage_Core_Model_Locale::FORMAT_TYPE_LONG,
        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
        Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
    );
    const DATETIME_INTERNAL_FORMAT = 'yyyy-MM-dd HH:mm:ss';
*/
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Snmportal\Pdfprint\Model\Pdf\Barcode\Barcode $barcode,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        array $data = []
    ) {
        $this->barcodeGenerator = $barcode;
        $this->_localeDate = $localeDate;
        $this->groupRepository = $groupRepository;
        $this->_localeLists=$localeLists;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context,$registry,$resource,$resourceCollection );
    }
    public function formatdate($store,$date,$format=\IntlDateFormatter::MEDIUM,$showTime = false)
    {
        return $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate($store, $date, true),
            $format,
            $showTime
        );
    }

    protected function _isConst($a)
    {
        if ( !$a )
            return true;
        $cl = $this->getProcessor()->getAuitVariableLine();
        if ( strpos($cl,"'".$a."'") !== false )
            return true;
        if ( strpos($cl,'"'.$a.'"') !== false )
            return true;
        return false;
    }

    function getValue($a,$noObject=true)
    {
        if ( !$this->getProcessor() )
            return $a;
        if ( is_numeric($a)) // 07.12.15
        {
            return $a;
        }
        if ( $this->_isConst($a) )
        {
            // Konstante
            return $a;
        }

        //$cl = $this->getProcessor()->getAuitVariableLine();
        if ( !$noObject && $this->_isConst($a) )
        {
            // Konstante
            return $a;
        }
        if ( !is_null($a) )
        {
            $r = null;
            try{
   //             set_error_handler('AuIt_ErrorHandler');
                $r = $this->getProcessor()->filter('{{var '.$a.'}}');
                if ( $r === 'Object' )
                {
                    $vars = $this->getProcessor()->getVariables();
                    if ( isset($vars[$a]) ) {
                        $r=$vars[$a];
                    }
                }


                if ( is_object($r) || $r == '')
                {
                    if ( $r instanceof \Magento\Framework\Api\DataObject )
                    {
                        if ( !$noObject )
                            $r=null;
                        else
                            $r = implode(',', $r->debug());
                    }
                }

            }catch ( \Exception $e)
            {
                $r = null;
            }
      //      set_error_handler(Mage_Core_Model_App::DEFAULT_ERROR_HANDLER);

            if ( is_null($r)  ) // 03.03.15 Empty Value nicht zurück setzen
            {
                $r=$a;
            }
            //if ( !$r ) $r=$a;
            return $r;
        }
        return $a;
    }
    function eq($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a == $b): false;
    }
    function neq($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a != $b): false;
    }
    function lt($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a < $b): false;
    }
    function lteq($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a <= $b): false;
    }
    function gt($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a > $b): false;
    }
    function gteq($a=null,$b=null)
    {
        $a = $this->getValue($a,false);
        $b = $this->getValue($b,false);
        return ( !is_null($a) && !is_null($b)) ? ($a >= $b): false;
    }
    function nl2br($a=null)
    {
        return nl2br(trim($this->getValue($a)));
    }
    function tolower($a=null)
    {
        $a = $this->getValue($a);
        return strtolower($a);
    }
    function toupper($a=null)
    {
        $a = $this->getValue($a);
        return strtoupper($a);
    }

    function country($a=null,$b=null)
    {
        $r='';
        $a = $this->getValue($a);
        if ( $a )
            $r = $this->_localeLists->getCountryTranslation($a);
        if ( $b == 1 )
            $r=strtoupper($r);
        if ( $b == 2 )
            $r=strtolower($r);
        return $r;
    }
    function date($a=null,$b=0,$c='medium',$showtime=0)
    {
        if ( !is_numeric($b)  )
        {
            $b = (int)$this->getValue($b);
        }
        if ( $a )
            $a = $this->getValue($a);
        //$c = $this->getValue($c);

        $order = $this->getProcessor()->auitVariable('order');
        $format=null;
        switch ( $c )
        {
            case 'medium':
                $format=\IntlDateFormatter::MEDIUM;
                break;
            case 'short':
                $format=\IntlDateFormatter::SHORT;
                break;
            case 'long':
                $format=\IntlDateFormatter::LONG;
                break;
            case 'full':
                $format=\IntlDateFormatter::FULL;
                break;
        }
        if ( $format )
        {
            if ( $b )
                return $this->formatdate($order->getStore(),$a.(" $b days"),$format,$showtime);
            return $this->formatdate($order->getStore(),$a,$format,$showtime);
        }
        $date = $b ? $this->_localeDate->date($a.(" $b days")) :$this->_localeDate->date($a);
        // http://php.net/manual/en/function.date.php
        $df = $date->format($c);
        return $df;
        /*
        $invoice = $this->getProcessor()->auitVariable('invoice');
        if ( $a == 'null' || $a == 'now')
        {
            $a = Mage::app()->getLocale()->date(Mage::getSingleton('core/date')->gmtTimestamp(), null, null);
        }
        else {
            $a = $this->getProcessor()->auitVariable($a);
        }
        if (!($a instanceof Zend_Date)) {
            $a = new Zend_Date($a, self::DATETIME_INTERNAL_FORMAT, null);
        }else {
            $a->setTimezone('GMT');
        }
        $result = 'not a date';
        if ($a instanceof Zend_Date) {

            if (in_array($c, $this->_allowedFormats, true)) {
                $result = Mage::helper('core')->formatDate($a.(" $b days"), $c, $showtime?true:false);
            }else {
                $format = trim($c);
                $date = $a;
                if (is_null($date)) {
                    $date = Mage::app()->getLocale()->date(Mage::getSingleton('core/date')->gmtTimestamp(), null, null);
                } else if (!$date instanceof Zend_Date) {
                    $date = Mage::app()->getLocale()->date(strtotime($date), null, null);
                }
                $result = $date->toString($format);
            }
        }
        return $result;
        */
        return 'DATE ????';
    }
    function hasGiftMessage()
    {
        $order = $this->getProcessor()->auitVariable('order');
        if ( $order && is_object($order) && $order->getGiftMessageId() )
            return true;
        return false;
    }
    function hasComments()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        $_collection = null;
        if ( $entity && $entity instanceof \Magento\Sales\Model\Order )
            $_collection = $entity->getStatusHistoryCollection();
        else if ( $entity )
            $_collection = $entity->getCommentsCollection();
        if ( $_collection && count($_collection) )
            return true;
        return false;
    }
    function hasVisibleComments()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        $_collection = null;
        if ( $entity && $entity instanceof \Magento\Sales\Model\Order )
            $_collection = $entity->getStatusHistoryCollection();
        else if ( $entity )
            $_collection = $entity->getCommentsCollection();
        if ( $_collection && count($_collection) )
        {
            foreach ($_collection as $_comment)
                if ( $_comment->getIsVisibleOnFront() )
                    return true;
        }
        return false;
    }
    function getCommentsCollection()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        $_collection = null;
        if ( $entity && $entity instanceof \Magento\Sales\Model\Order )
            $_collection = $entity->getStatusHistoryCollection();
        else if ( $entity )
            $_collection = $entity->getCommentsCollection();
        return $_collection;
    }
    protected function _isCountryInEU($countryCode, $storeId = null)
    {
        $euCountries = explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_EU_COUNTRIES_LIST,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        return in_array($countryCode, $euCountries);
    }
    function isCountryInEU($countryCode,$isString=false)
    {
        if ( !$isString )
        {
            $countryCode = trim($this->getValue($countryCode));
        }
        if ( !$countryCode ) // AUIT 19.03.2013 empty codes
        {
            return false;
        }
        if ( 1 )
        {
            $order = $this->getProcessor()->auitVariable('order');
            $storeId = null;
            if ( $order ) {
                $storeId = $order->getStore()->getId();
            }
            $ret = $this->_isCountryInEU($countryCode,$storeId);
            return $ret;
        }
        return false;
    }
    function isCountryNotInEU($countryCode)
    {
        return !$this->isCountryInEU($countryCode);
    }
    function getVatID($country_id=null,$vat_id=null,$country_id2=null,$vat_id2=null,$taxvat=null)
    {
        $cid=false;
        $vid=false;
        if ( !is_null($country_id) )
        {
            $countryCode = trim($this->getValue($country_id));
            if ( $country_id != $countryCode && strlen ($countryCode) == 2 )
            {
                $cid=$country_id;
                $vid = $vat_id;
            }
        }
        if ( !$cid && !is_null($country_id2) )
        {
            $countryCode = trim($this->getValue($country_id2));
            if ( $country_id2 != $countryCode && strlen ($countryCode) == 2 )
            {
                $cid=$country_id2;
                $vid = $vat_id2;
            }
        }
        if ( $cid && $vid)
        {
            $vidCode = trim($this->getValue($vid));
            if ( $vidCode != $vid && $vidCode) // Variable not set
            {
                $countryCode = strtoupper(trim($this->getValue($cid)));
                $vidCode = strtoupper(trim($vidCode));
                if (substr($vidCode, 0, strlen($countryCode)) == $countryCode) {
                    return $vidCode;
                }
                return strtoupper(trim($countryCode.$vidCode));
            }
        }
        if ( !is_null($taxvat) )
        {
            $vidCode = trim($this->getValue($taxvat));
            if ( $vidCode != $taxvat && $vidCode) // Variable not set
                return strtoupper(trim($vidCode));
        }
        return '';
    }

    function isEUVATTaxFree($tax_amount=null,$country_id=null,$vat_id=null,$country_id2=null,$vat_id2=null,$taxvat=null)
    {
        if ( !is_null($tax_amount) )
        {
            $vidCode = $this->getVatID($country_id,$vat_id,$country_id2,$vat_id2,$taxvat);
            if ( strlen($vidCode) > 2 )
            {
                $cid = substr($vidCode,0,2);
                if ( $cid == 'EL' ) $cid = 'GR';
                $ta = floatval($this->getValue($tax_amount));
                if ( (''.$ta != $tax_amount && $ta == 0)  )
                {
                    if ( $this->isCountryInEU($cid,true) )
                    {
                        return 1;
                    }
                }
            }
        }
        return 0;
    }
    function isWorldTaxFree($tax_amount=null,$country_id=null,$country_id2=null)
    {
        if ( is_null($tax_amount) )
            return 0;
        $tax_amount = floatval($this->getValue($tax_amount));
        if ( !$tax_amount )
        {
            $cid=false;
            // Check for emtpy conutryid 19.03.2013
            if ( !is_null($country_id) )
            {
                $countryCode = trim($this->getValue($country_id));
                if ( $country_id != $countryCode && strlen ($countryCode) == 2 )
                    $cid=$country_id;
            }
            if ( !$cid && !is_null($country_id2) )
            {
                $countryCode = trim($this->getValue($country_id2));
                if ( $country_id2 != $countryCode && strlen ($countryCode) == 2 )
                    $cid=$country_id2;
            }
            if ( $cid && !$this->isCountryInEU($cid) )
            {
                return 1;
            }
        }
        return 0;
    }

    function getCustomerGroupName($customerGroupId)
    {
        $customerGroupId = $this->getValue($customerGroupId);
        //if ($this->getOrder()) {
            //$customerGroupId = $this->getOrder()->getCustomerGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (\NoSuchEntityException $e) {
                return '';
            }
        //}
        return '';
//        $customer_group_id = $this->getValue($customer_group_id);
//        return Mage::getModel('customer/group')
  //          ->load($customer_group_id)
    //        ->getCustomerGroupCode();
    }
    function roundPrice($price)
    {
        //$price = $this->getValue($price);
        return $this->round($price);
    }
    function round($price,$anzahl=2)
    {
        $price = $this->getValue($price);
        $anzahl= $this->getValue($anzahl,false);
        return round($price, (int)$anzahl);
    }
    function formatPrice($price,$addBrackets=0)
    {
        $price = $this->getValue($price);
        $addBrackets = $this->getValue($addBrackets);
        $order = $this->getProcessor()->auitVariable('order');
        return $order->formatPrice($price, $addBrackets);
    }
    function isShippingNeqBillingAddress()
    {
        return !$this->isShippingEqBillingAddress();
    }
    function isShippingEqBillingAddress()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        if ( !$entity->getShippingAddress() )
            return true;

        $billingAdress = $entity->getBillingAddress();
        $shippingAdress = $entity->getShippingAddress();
        if ( !$shippingAdress || !$billingAdress)
            return true;
        foreach ( array('postcode','lastname','firstname','street','city','country_id') as $code )
            if ( $billingAdress->getData($code) != $shippingAdress->getData($code) )
                return false;
        return true;
    }
    function hasBillingAddress()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        return $entity && $entity->getBillingAddress()?true:false;
    }
    function hasShippingAddress()
    {
        $entity = $this->getProcessor()->auitVariable('entity');
        return $entity && $entity->getShippingAddress()?true:false;
    }
    function getBarcode1D($type, $code,$w = 2, $h = 30, $color = 'black')
    {
        $type = $this->getValue($type);
        $code = $this->getValue($code);
        return $this->barcodeGenerator->getBarcode1D($code,$type,$w, $h, $color );
    }
    function getBarcode2D($type, $code, $w = 10, $h = 10, $color = 'black')
    {
        $type = $this->getValue($type);
        $code = $this->getValue($code);
        return $this->barcodeGenerator->getBarcode2D($code,$type,$w, $h, $color );
    }
    function getBarcodeGenerator()
    {
        return $this->barcodeGenerator;
    }
}