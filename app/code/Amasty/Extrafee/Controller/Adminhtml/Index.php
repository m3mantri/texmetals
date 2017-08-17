<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml;

/**
 * Class Index
 *
 * @author Artem Brunevski
 */

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Amasty\Extrafee\Controller\RegistryConstants;
use Amasty\Extrafee\Model\FeeRepository;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\Page;
use Amasty\Extrafee\Model\FeeFactory;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Extrafee\Model\ResourceModel\Fee\CollectionFactory as FeeCollectionFactory;

abstract class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Extrafee::manage';

    /** @var ForwardFactory  */
    protected $_resultForwardFactory;

    /** @var PageFactory  */
    protected $_resultPageFactory;

    /** @var Registry  */
    protected $_coreRegistry;

    /** @var FeeRepository  */
    protected $_feeRepository;

    /** @var FeeFactory  */
    protected $_feeFactory;

    /** @var Filter  */
    protected $_filter;

    /** @var FeeCollectionFactory  */
    protected $_feeCollectionFactory;

    /**
     * @param Action\Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param FeeRepository $feeRepository
     * @param FeeFactory $feeFactory
     * @param Filter $filter
     * @param FeeCollectionFactory $feeCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        FeeRepository $feeRepository,
        FeeFactory $feeFactory,
        Filter $filter,
        FeeCollectionFactory $feeCollectionFactory
    ){
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_feeRepository = $feeRepository;
        $this->_feeFactory = $feeFactory;
        $this->_filter = $filter;
        $this->_feeCollectionFactory = $feeCollectionFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Amasty\Extrafee\Api\Data\FeeInterface|\Amasty\Extrafee\Model\Fee
     * @throws \Amasty\Extrafee\Model\NoSuchEntityException
     */
    protected function initCurrentFee()
    {
        $feeId = $this->getRequest()->getParam('id');
        $fee = $this->_feeRepository->create();
        if ($feeId) {
            $fee = $this->_feeRepository->getById($feeId);
        }
        $this->_coreRegistry->register(RegistryConstants::FEE, $fee);
        return $fee;
    }

    /**
     * @param Page $resultPage
     */
    protected function prepareDefaultTitle(Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Fees'));
    }
}