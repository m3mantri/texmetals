<?php
namespace Snmportal\Pdfprint\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Snmportal\Pdfprint\Model\Template;

class AbstractFrontendPlugin extends AbstractPlugin
{
    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderAuthorization,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper
    ) {
        $this->orderAuthorization=$orderAuthorization;
        $this->_objectManager = $context->getObjectManager();
        parent::__construct($pdfHelper);
    }
}
