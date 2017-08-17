<?php
namespace Snmportal\Pdfprint\Block\Adminhtml\Renderer;

/**
 * Form fieldset renderer
 */
class File extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Form element which re-rendering
     *
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'renderer/file.phtml';

    /**
     * Retrieve an element
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    /**
     * Return html for store switcher hint
     *
     * @return string
     */

//    public function getHintHtml()
//    {
//        /** @var $storeSwitcher \Magento\Backend\Block\Store\Switcher */
//        $storeSwitcher = $this->_layout->getBlockSingleton('Magento\Backend\Block\Store\Switcher');
//        return $storeSwitcher->getHintHtml();
//    }

}
