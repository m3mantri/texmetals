<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Adminhtml\Fee\Edit\Tab;

/**
 * Class Calculation
 *
 * @author Artem Brunevski
 */

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Extrafee\Controller\RegistryConstants;

class Calculation extends Generic implements TabInterface
{
    /** @var \Amasty\Extrafee\Model\Config\Source\Excludeinclude  */
    protected $excludeincludeSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Amasty\Extrafee\Model\Config\Source\Excludeinclude $excludeincludeSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Extrafee\Model\Config\Source\Excludeinclude $excludeincludeSource,
        array $data = []
    ){
        $this->excludeincludeSource = $excludeincludeSource->setUseDefaultOption(true);
        parent::__construct(
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
        return __('Calculation');
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
            'calculation_fieldset',
            ['legend' => __('Calculation'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'discount_in_subtotal',
            'select',
            [
                'name' => 'discount_in_subtotal',
                'label' => __('Include discount in subtotal'),
                'title' => __('Include discount in subtotal'),
                'values' => $this->excludeincludeSource->toOptionArray()
            ]
        );

        $fieldset->addField(
            'tax_in_subtotal',
            'select',
            [
                'name' => 'tax_in_subtotal',
                'label' => __('Include tax in subtotal'),
                'title' => __('Include tax in subtotal'),
                'values' => $this->excludeincludeSource->toOptionArray()
            ]
        );

        $fieldset->addField(
            'shipping_in_subtotal',
            'select',
            [
                'name' => 'shipping_in_subtotal',
                'label' => __('Include shipping in subtotal'),
                'title' => __('Include shipping in subtotal'),
                'values' => $this->excludeincludeSource->toOptionArray()
            ]
        );

        if (!$model->getId()){
            $model->setData([
                'discount_in_subtotal' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT,
                'tax_in_subtotal' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT,
                'shipping_in_subtotal' => \Amasty\Extrafee\Model\Config\Source\Excludeinclude::VAR_DEFAULT
            ]);
        }

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}