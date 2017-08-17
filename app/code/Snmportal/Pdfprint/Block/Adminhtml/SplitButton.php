<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace   Snmportal\Pdfprint\Block\Adminhtml;

class SplitButton  extends \Magento\Backend\Block\Widget\Button\SplitButton
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core registry
     *
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_pdfHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Snmportal\Pdfprint\Helper\Template $pdfHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_pdfHelper = $pdfHelper;
        parent::__construct($context, $data);
        $this->setButtonClass('pdfprint');
    }

    public function getButtonAttributesHtml()
    {
        $this->setStyle('min-width:inherit;background-color: transparent;    border-color: transparent;    text-shadow: none;    color: #41362f;');

        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-default';
        //  $classes[] = 'primary';
        // @TODO Perhaps use $this->getButtonClass() instead
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = [
            'id' => $this->getId() . '-button',
            'title' => $title,
            'onclick' => $this->getOnclick(),
            'class' => join(' ', $classes),
            'disabled' => $disabled,
            'style' => $this->getStyle(),
        ];

        //TODO perhaps we need to skip data-mage-init when disabled="disabled"
        if ($this->getDataAttribute()) {
            $this->_getDataAttributes($this->getDataAttribute(), $attributes);
        }

        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId();

        return $html;
    }
    public function getToggleAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-toggle';
//        $classes[] = 'primary';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        $hide = !count($this->getOptions()) ?'display:none':'';

        $attributes = [ 'title' => $title,
            'class' => join(' ', $classes),
            'disabled' => $disabled,
            'style'=>'background-color: transparent;  border-color: transparent;   '.$hide];


        $this->_getDataAttributes(['mage-init' => '{"dropdown": {}}', 'toggle' => 'dropdown'], $attributes);

        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId('dropdown');

        return $html;
    }
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        //$html = str_replace( '<ul','<ul style="right:inherit;white-space: nowrap;"',$html);
        return $html;
    }

}
