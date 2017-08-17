<?php
namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;
class Style extends \Magento\Backend\Block\Widget\Form\Generic implements
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


        $fieldset = $form->addFieldset('layout2_margins1', ['legend' => __('Margins Page 1')]);
        $field = $fieldset->addField(
            'margins1',
            'text',
            [
                'name' => 'margins1',
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Margins'));
        $fieldset = $form->addFieldset('layout2_margins2', ['legend' => __('Margins Page 2-n')]);
        $field = $fieldset->addField(
            'margins2',
            'text',
            [
                'name' => 'margins2',
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Margins'));

        $fieldset = $form->addFieldset('layout2_text_before', ['legend' => __('CSS Style')]);
        $field = $fieldset->addField(
            'css',
            'textarea',
            [
                'name' => 'css',
                //'style'=>'height:502px;',
                'label' => __('CSS Style'),
                'title' => __('CSS Style'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'codemode'=>'css'
            ]
        );
        $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\Codestyle'));

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
        return __('Style');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Style');
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
        return (bool)$this->getModelInstance()->isCompleteToCreate();
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
