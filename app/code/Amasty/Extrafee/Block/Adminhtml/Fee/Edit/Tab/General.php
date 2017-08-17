<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Adminhtml\Fee\Edit\Tab;

/**
 * Class General
 *
 * @author Artem Brunevski
 */

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Extrafee\Controller\RegistryConstants;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Amasty\Extrafee\Model\Fee\Source\FrontendType;

class General extends Generic implements TabInterface
{
    /** @var Yesno  */
    protected $yesno;

    /** @var FrontendType  */
    protected $frontendType;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesno
     * @param FrontendType $frontendType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesno,
        FrontendType $frontendType,
        array $data = []
    ) {
        $this->yesno = $yesno;
        $this->frontendType = $frontendType;

        return parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /** @var \Amasty\Extrafee\Model\Fee $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEE);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'enabled',
            'select',
            [
                'name' => 'enabled',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $this->yesno->toOptionArray()
            ]
        );

        $fieldset->addField(
            'frontend_type',
            'select',
            [
                'name' => 'frontend_type',
                'label' => __('Type'),
                'title' => __('Type'),
                'values' => $this->frontendType->toOptionArray()
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort_ Order'),
                'title' => __('Sort Order'),
                'class' => 'validate-number',
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description')
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

}