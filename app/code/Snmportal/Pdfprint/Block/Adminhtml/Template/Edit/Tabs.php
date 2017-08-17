<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder,$authSession);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('template_record');
        $this->setDestElementId('edit_form');
        $model = $this->getModelInstance();
        if ( $model )
            $this->setTitle(__('Template: %1',''.$model->getTypeName()));
        else
            $this->setTitle(__('Template'));
    }

    public function getModelInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }

}