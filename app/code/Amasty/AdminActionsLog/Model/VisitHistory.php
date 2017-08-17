<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Model;

use Magento\Framework\Model\AbstractModel;

class VisitHistory extends AbstractModel
{
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_helper;

    protected function _construct()
    {
        $this->_init('Amasty\AdminActionsLog\Model\ResourceModel\VisitHistory');
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\AdminActionsLog\Helper\Data $helper
    )
    {
        parent::__construct($context, $coreRegistry);
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
    }

    public function startVisit($userData)
    {
        $userData['session_start'] = time();
        $userData['session_id'] = $this->_helper->getSessionId();
        $this->setData($userData);
        $this->save();
    }

    public function getVisitEntity($sessionId)
    {
        $activeModel = $this->getCollection()
            ->addFieldToFilter('session_id', $sessionId);
        $activeEntity = $activeModel->getFirstItem();
        return $activeEntity;
    }

    public function endVisit($sessionId, $observer = NULL)
    {
        $visitEntity = $this->load($sessionId, 'session_id');
        if (!$visitEntity->getId()) {
            /** @var $loginAttemptsModel \Amasty\AdminActionsLog\Model\LoginAttempts $logModel */
            $loginAttemptsModel = $this->_objectManager->create('Amasty\AdminActionsLog\Model\LoginAttempts');
            $userData = $loginAttemptsModel->prepareUserLoginData($observer, \Amasty\AdminActionsLog\Model\LoginAttempts::LOGOUT);
            $visitEntity->setData($userData);
        }
        $visitEntity->addData(array('session_end' => time()));
        $visitEntity->save();

        /**
         * @var \Amasty\AdminActionsLog\Model\VisitHistoryDetails $detailModel
         */
        $detailModel = $this->_objectManager->get('Amasty\AdminActionsLog\Model\VisitHistoryDetails');

        $detailModel->saveLastPageDuration($this->_helper->getSessionId());
    }

    public function clearLog($fromObserver = true)
    {
        $logCollection = $this->getCollection();

        $where = [];

        if ($fromObserver) {
            $days = $this->_scopeConfig->getValue('amaudit/log/log_delete_pages_history_after_days');
            $where['session_start < NOW() - INTERVAL ? DAY'] = $days;
        }

        $logCollection->getConnection()->delete($logCollection->getMainTable(), $where);
    }
}
