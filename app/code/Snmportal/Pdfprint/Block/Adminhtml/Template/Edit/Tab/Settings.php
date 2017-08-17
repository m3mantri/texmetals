<?php
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

class Settings extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Snmportal\Pdfprint\Model\Options\TypeHash
     */
    protected $typeOptions;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Snmportal\Pdfprint\Model\Options\TypeHash $typeOptions,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->typeOptions = $typeOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return !(bool)$this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
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
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
          //  ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        /*


        */

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        $this->_addElementTypes($fieldset);
        $fieldset->addField(
            'type',
            'select',
            [
                'name' => 'type',
                'label' => __('Type'),
                'title' => __('Type'),
                'required' => true,
                'values' => $this->getTypesOptionsArray()
            ]
        );

        $field = $fieldset->addField(
            'file_pdf_import',
            'file',
            [
                'name' => 'file_pdf_import',
                'label' => __('Import File'),
                'title' => __('Import File'),
                'display' => 'none',
                'required' => true,
                'note' => __('Please use (*.snmportal-pdfprint-template) File').
                    __('<br/><a target="_blank" href="https://snm-portal.com/pdfprint-m2-example-templates">Download Sample Templates from SNM-Portal</a>')
            ]
        );
        //$field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
        $continueButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Continue'),
//                'onclick' => "setSettings('" . $this->getContinueUrl() . "', 'type')",
//                'onclick' => "this.form.submit()",
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]

            ]
        );
        $fieldset->addField('continue_button', 'note', ['text' => $continueButton->toHtml()]);

        $this->setForm($form);
        $htmlIdPrefix = $form->getHtmlIdPrefix();


        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}type",
                'type'
            )->addFieldMap(
                    "{$htmlIdPrefix}file_pdf_import",
                    'file_pdf_import'
            )
            ->addFieldDependence(
                'file_pdf_import',
                'type',
                'import_file'
            )
        );
        return parent::_prepareForm();
    }

    /**
     * Return url for continue button
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl(
            'snmpdfprint/*/*',
            ['_current' => true, 'type' => '<%- data.type %>']
        );
    }


    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     */

    public function getTypesOptionsArray()
    {
        $options = $this->typeOptions->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);
        $options[]=['value' => 'import_file', 'label' => __('Import from File')];
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
}
