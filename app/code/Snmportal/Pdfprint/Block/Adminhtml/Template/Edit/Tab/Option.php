<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

class Option extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var $model \Snmportal\Pdfprint\Model\Template */
        $model = $this->_coreRegistry->registry('snmportal_pdfprint_template');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('layout_');
//        $fieldset = $form->addFieldset('upload_file_fieldset', ['legend' => __('Template Information')]);

        // fieldset for file uploading
        $fieldsets['upload'] = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import'), 'class' => 'no-displayxxx']
        );



/*

        $fieldsets['upload']->addField(
            'template_name',
            'label',
            [
                'name' => 'template_name',
                'label' => __('File name'),
                'title' => __('File name'),

            ]
        );
        if ( $model->getTemplateName() )
        {
            $fieldsets['upload']->addField(
                'template_name_delete',
                'checkbox',
                [
                    'name' => 'template_name_delete',
                    'label' => __('File delete'),
                    'title' => __('File delete'),

                ]
            );
        }
        $fieldsets['upload']->addField(
            'import_file',
            'file',
            [
                'name' => 'import_file',
                'label' => __('Select File to Import'),
                'title' => __('Select File to Import'),
                //'required' => true,
                'class' => 'input-file'
            ]
        );*/

        // $model->setData('import_file', 'test...pdf');
        //     $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('template_');

        $fieldset = $form->addFieldset(
            'content_fieldset',
            ['legend' => __('Content'), 'class' => 'fieldset-wide']
        );

        /*
                $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
                $fieldset->addField(
                    'content_heading',
                    'text',
                    [
                        'name' => 'content_heading',
                        'label' => __('Content Heading'),
                        'title' => __('Content Heading'),
                        'disabled' => $isElementDisabled
                    ]
                );

                $contentField = $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name' => 'content',
                        'style' => 'height:36em;',
                        'required' => true,
                        'disabled' => $isElementDisabled,
                        'config' => $wysiwygConfig
                    ]
                );

                // Setting custom renderer for content field to remove label column
                $renderer = $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element'
                )->setTemplate(
                    'Magento_Cms::page/edit/form/renderer/content.phtml'
                );
                $contentField->setRenderer($renderer);

        */
        $this->_eventManager->dispatch('snmportal_pdfprint_template_edit_tab_content_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Option');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Option');
    }

    /**
     * Getter
     *
     * @return \Snmportal\Pdfprint\Model\Template
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return (bool)$this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
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
}
