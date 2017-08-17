<?php
namespace VladimirPopov\AwHdIntegration\Block\Adminhtml\Form\Edit\Tabs;

class Helpdesk extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Helpdesk Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Helpdesk Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected $departmentsSource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [],
        \Aheadworks\Helpdesk\Model\Source\Departments $departmentsSource
    ) {
        $this->departmentsSource = $departmentsSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Form */
        $model = $this->_coreRegistry->registry('webforms_form');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('VladimirPopov_WebForms::manage_forms')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('form_');
        $form->setFieldNameSuffix('form');


        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element_renderer'
            )
        );
        $form->setDataObject($model);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Aheadworks Helpdesk Integration')]);

        $fieldset->addField(
            'awheldesk_create_tickets',
            'select',
            [
                'label' => __('Create tickets'),
                'title' => __('Create tickets'),
                'note' => __('Create new tickets on form submission'),
                'name' => 'awheldesk_create_tickets',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $default = array('0' => __('Default'));
        $departments = $this->departmentsSource->toOptionArray();
        $department_options = $default + $departments;

        $fieldset->addField(
            'awhelpdesk_default_department',
            'select',
            [
                'name' => 'awhelpdesk_default_department',
                'label' => __('Default department'),
                'title' => __('Default department'),
                'values' => $department_options,
                'disabled' => $isElementDisabled
            ]
        );

        $this->_eventManager->dispatch('adminhtml_webforms_form_edit_tab_customer_registration_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
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