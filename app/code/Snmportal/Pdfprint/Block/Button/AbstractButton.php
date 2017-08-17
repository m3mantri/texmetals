<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Button;
use Magento\Customer\Model\Context;

abstract class AbstractButton extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'print/button.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }


    public function _getDefaultCss()
    {
        $style='<style>';
        $style .= 'body .order-details-items .action.print{display:block;}';
  //      $style .= '.actions .action.print{display:none;}';
    //    $style .= '.actions .action.print.snmportal{display:block;}';
        $style.='</style>';
        return $style;
    }
    protected function _toHtml()
    {
        return '';
    }

}

