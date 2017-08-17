<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class Edit
 *
 * @author Artem Brunevski
 */
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Amasty\Extrafee\Model\Fee;
use Amasty\Extrafee\Controller\RegistryConstants;

class Edit extends Index
{
    /**
     * @param Fee $fee
     * @return \Magento\SalesRule\Model\Rule
     */
    protected function initCurrentFeeRule(
        Fee $fee
    ){
        $rule = $this->_feeRepository->getSalesRule($fee);
        $this->_coreRegistry->register(RegistryConstants::FEE_RULE, $rule);
        return $rule;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $fee = $this->initCurrentFee();
        $rule = $this->initCurrentFeeRule($fee);

        $rule->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Extrafee::fee_manage');
        $this->prepareDefaultTitle($resultPage);
        $resultPage->setActiveMenu('Magento_Customer::fee');

        if ($fee->getId()) {
            $resultPage->getConfig()->getTitle()->prepend($fee->getName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Fee'));
        }
        return $resultPage;
    }
}