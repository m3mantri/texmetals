<?php
namespace Snmportal\Pdfprint\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $name,
        array $handlers = array(),
        array $processors = array()
    )
    {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers,$processors);
    }

    public function addRecord($level, $message, array $context = array())
    {
        if ( $this->getStoreConfig('snmportal_pdfprint/general/logging') )
            parent::addRecord($level, $message, $context);
    }
    protected function getStoreConfig($pfad,$store=null)
    {
        return  $this->_scopeConfig->getValue(
            $pfad,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }


}