<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace Snmportal\Pdfprint\Block\Adminhtml\Template;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'template_id';
        $this->_blockGroup = 'Snmportal_Pdfprint';
        $this->_controller = 'adminhtml_template';

        parent::_construct();

        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {

            if ($this->getToolbar()) {
                $this->getToolbar()->addChild(
                    'save-split-button',
                    'Magento\Backend\Block\Widget\Button\SplitButton',
                    [
                        'id' => 'save-split-button',
                        'label' => __('Save'),
                        'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                        'button_class' => 'widget-button-save',
                        'options' => $this->_getSaveSplitButtonOptions()
                    ]
                );
            }
            $this->buttonList->remove('save');
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_delete')) {
            //$this->buttonList->update('delete', 'label', __('Delete Grid'));
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }

    }
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];
            $options[] = [
                'id' => 'edit-button',
                'label' => __('Save & Edit'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ],
                'default' => true,
            ];

        $options[] = [
            'id' => 'new-button',
            'label' => __('Save & Duplicate'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndDuplicate', 'target' => '#edit_form'],
                ],
            ],
        ];
        $options[] = [
            'id' => 'close-button',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
            ],
        ];
        return $options;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('snmportal_pdfprint_template')->getId()) {
            return __("Edit Template '%1'", $this->escapeHtml($this->_coreRegistry->registry('snmportal_pdfprint_template')->getTitle()));
        } else {
            return __('New Template');
        }
    }


    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        $this->_formScripts[] = "
require([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function($){
        var f = $('#edit_form').form();
        var fm = f.mage('form');
        $('#edit_form').form().mage('form', {
        handlersData: {
        save: {},
        saveAndContinueEdit: {
            action: {
                args: {back: 'edit'}
            }
        },
        saveAndNew: {
            action: {
                args: {back: 'new'}
            }
        },
        saveAndDuplicate: {
            action: {
                args: {back: 'duplicate'}
            }
        }
    }
    });
});        ";
/*
        $this->_formScripts[] = "
        ";
*/
        return parent::_prepareLayout();
    }

}