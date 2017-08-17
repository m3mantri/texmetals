<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace   Snmportal\Pdfprint\Block\Adminhtml;

class DropdownButton extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _getDefaultCss()
    {
        return '';
    }
    protected function _toHtml()
    {
        if ( count($this->_getTemplatesOptions()) )
            return $this->_getDefaultCss().$this->_dropdownButtonHTML();

        return $this->_getDefaultCss().'
        <button type="button" class="action-default scalable snmbutton" onclick="setLocation('.$this->getButtonUrl().')">
            <span >'.$this->getButtonLabel().'</span>
        </button>';
    }

    protected function _dropdownButtonHTML()
    {
        return '
    <div class="admin-snm-dropdown admin__action-dropdown-wrap">
    <button
        type="button"
        class="action-defaultxx admin__action-dropdown"
        data-mage-init=\'{"dropdown":{}}\'
        data-toggle="dropdown">
        <span class="admin__action-dropdown-text"><span class="xx">'.$this->getButtonLabel().'</span></span>
    </button>
    <ul class="admin__action-dropdown-menu" >'.$this->_getTemplatesOptionsHtml().'</ul></div>';

    }
    public function getButtonLabel()
    {
        return 'ButtonLabel';
    }
    protected function _getTemplatesOptionsHtml()
    {
        $html = '';
        foreach ( $this->_getTemplatesOptions() as $option )
        {
            $html.='<li>';
            $html.='<a href="'.$option['url'].'">'.$option['label'].'</a>';
            $html.='</li>';
        }
        return $html;
    }

    protected function _getTemplatesOptions()
    {
        $options = [];
        return $options;
    }

}
