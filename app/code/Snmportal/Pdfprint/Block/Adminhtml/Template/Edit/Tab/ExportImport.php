<?php
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;
class ExportImport extends \Magento\Backend\Block\Widget\Form\Generic implements
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
        $model = $this->getModelInstance();

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
        if ( $model->getId() )
        {
            $fieldset = $form->addFieldset('expimp_export', ['legend' => __('Export')]);
            $fontButton = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'id' => 'export_manager',
                    'label' => __('Export Template'),
                    'class' => 'button',
                    'onclick' => "setLocation('" . $this->getUrl('snmpdfprint/template/export',['template_id' => $model->getId()])."');",
                ]
            );
            $fieldset->addField(
                'export_button',
                'note',
                ['label' => '', 'text' => $fontButton->toHtml()]
            );
        }

/*
        $fieldset = $form->addFieldset('expimp_import', ['legend' => __('Import')]);

        $field = $fieldset->addField(
            'file_pdf_import',
            'file',
            [
                'referenz_field'=>'import_file',
                'referenz_value'=>'',
                'delete_field'=>'import_file_delete',
                'delete_label'=>__('Delete File'),
                'name' => 'import_file',
                'label' => __('Import File'),
                'title' => __('Import File'),
                'class' => 'input-file',
                'note' => __('Please use (*.snmportal-pdfprint-template) File'),
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
*/

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
        return __('Export Template');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Export Template');
    }

    /**
     * Getter
     *
     * @return \Snmportal\Pdfprint\Model\Template
     */
    public function getModelInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return (bool)$this->getModelInstance()->isCompleteToCreate() && $this->_isAllowedAction('Snmportal_Pdfprint::template_save');
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
