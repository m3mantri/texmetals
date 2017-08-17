<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

class Background extends \Magento\Backend\Block\Widget\Form\Generic implements
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

        $fieldsets['upload'] = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('PDF Files'), 'class' => '']
        );
        $field = $fieldsets['upload']->addField(
            'file_pdf_background',
            'file',
            [
                'referenz_field'=>'pdf_background',
                'referenz_value'=>$model->getPdfFilename('pdf_background'),
                'delete_field'=>'pdf_background_delete',
                'delete_label'=>__('Delete File'),
                'name' => 'file_pdf_background',
                'label' => __('PDF Background'),
                'title' => __('PDF Background'),
                //'required' => true,
                'class' => 'input-file',
                'note' => __('Please use PDF with version <= 1.4 as background template'),
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));

        $fieldsets['upload']->addField(
            'pdf_appendix_use',
            'select',
            [
                'label' => __('Use PDF Appendix'),
                'title' => __('Template Status'),
                'name' => 'pdf_appendix_use',
                'required' => false,
                'onchange' => 'hideShowPDFTemplate(this);',
                'options' => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );
        $field = $fieldsets['upload']->addField(
            'file_pdf_appendix',
            'file',
            [
                'referenz_field'=>'pdf_appendix',
                'referenz_value'=>$model->getPdfFilename('pdf_appendix'),
                'delete_field'=>'pdf_appendix_delete',
                'delete_label'=>__('Delete File'),
                'name' => 'file_pdf_appendix',
                'label' => __('PDF Appendix'),
                'title' => __('PDF Appendix'),
                //'required' => true,
                'class' => 'input-file'
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));

        $fieldsets['email'] = $form->addFieldset(
            'email_file_fieldset',
            ['legend' => __('Email Options')]
        );
        $fieldsets['email']->addField(
            'pdf_attachment1_use',
            'select',
            [
                'label' => __('Use Attachment 1'),
                'title' => __('Use Attachment 1'),
                'name' => 'pdf_attachment1_use',
                'required' => false,
                'onchange' => 'hideShowPDFTemplate(this);',
                'options' => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );
        $field = $fieldsets['email']->addField(
            'file_pdf_attachment1',
            'file',
            [
                'referenz_field'=>'pdf_attachment1',
                'referenz_value'=>$model->getPdfFilename('pdf_attachment1'),
                'delete_field'=>'pdf_attachment1_delete',
                'delete_label'=>__('Delete File'),
                'name' => 'file_pdf_attachment1',
                'label' => __('PDF Attachment 1'),
                'title' => __('PDF Attachment 1'),
                //'required' => true,
                'class' => 'input-file'
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
        $fieldsets['email']->addField(
            'pdf_attachment1_name',
            'text',
            [
                'label' => __('Name Attachment 1'),
                'title' => __('Name Attachment 1'),
                'name' => 'pdf_attachment1_name',
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldsets['email']->addField(
            'pdf_attachment2_use',
            'select',
            [
                'label' => __('Use Attachment 2'),
                'title' => __('Use Attachment 2'),
                'name' => 'pdf_attachment2_use',
                'onchange' => 'hideShowPDFTemplate(this);',
                'required' => false,
                'options' => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );
        $field = $fieldsets['email']->addField(
            'file_pdf_attachment2',
            'file',
            [
                'referenz_field'=>'pdf_attachment2',
                'referenz_value'=>$model->getPdfFilename('pdf_attachment2'),
                'delete_field'=>'pdf_attachment2_delete',
                'delete_label'=>__('Delete File'),
                'name' => 'file_pdf_attachment2',
                'label' => __('PDF Attachment 2'),
                'title' => __('PDF Attachment 2'),
                //'required' => true,
                'class' => 'input-file'
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
        $fieldsets['email']->addField(
            'pdf_attachment2_name',
            'text',
            [
                'label' => __('Name Attachment 2'),
                'title' => __('Name Attachment 2'),
                'name' => 'pdf_attachment2_name',
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
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
        return __('Pdf Templates');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Pdf Templates');
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
