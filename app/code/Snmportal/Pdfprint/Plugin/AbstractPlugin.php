<?php
namespace Snmportal\Pdfprint\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Snmportal\Pdfprint\Model\Template;

class AbstractPlugin
{
    /**
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_pdfHelper;

    public function __construct(
        \Snmportal\Pdfprint\Helper\Template $pdfHelper
    ) {
        $this->_pdfHelper = $pdfHelper;
    }
    protected function attachEmails(\Magento\Sales\Model\Order\Email\Sender $caller,$document,$typ,$store)
    {
        return $this->_pdfHelper->attachEmails($caller,$document,$typ,$store);
    }
    protected function getEngine($typ,$store)
    {
        return $this->_pdfHelper->getEngine($typ,$store);
    }
}
