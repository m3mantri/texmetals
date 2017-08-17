<?php

/**
 * A Magento 2 module named Icube/Ordernotifications
 * Copyright (C) 2016 Derrick Heesbeen
 * 
 * This file included in Icube/Ordernotifications is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */


namespace Icube\Ordernotifications\Mail;

class Transport extends \Zend_Mail_Transport_Sendmail implements \Magento\Framework\Mail\TransportInterface
{

    protected $_message;
    
    protected $_ordernotifications;
    
    protected $_parameters;
    
    protected $_templateOptions;
    
    protected $_templateVars;
    
    protected $_scopeConfig;

    public function __construct(\Magento\Framework\Mail\MessageInterface $message, \Icube\Ordernotifications\Model\OrdernotificationsFactory $ordernotifications, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, $parameters = null)
    {
        if (!$message instanceof \Zend_Mail) {
            throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
        }
        parent::__construct($parameters);
        $this->_message = $message;
        $this->_ordernotifications = $ordernotifications;
        $this->_parameters = $parameters;
        $this->_scopeConfig = $scopeConfig;
    }
    
    public function sendMessage()
    {
        
        $ordernotifications = $this->_ordernotifications->create();
        
        $ordernotifications->setBody($this->_message->getBody()->getRawContent());
        $ordernotifications->setSubject($this->_message->getSubject());
        $ordernotifications->setTo(implode(',',$this->_message->getRecipients()));
        $ordernotifications->setFrom($this->_message->getFrom());
        $ordernotifications->setCreatedAt(date('c'));
        $ordernotifications->save();
        
         try {
                parent::send($this->_message);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
            }
        
    }
}
