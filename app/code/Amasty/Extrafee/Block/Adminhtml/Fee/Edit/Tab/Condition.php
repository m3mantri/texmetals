<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Adminhtml\Fee\Edit\Tab;

/**
 * Class Condition
 *
 * @author Artem Brunevski
 */
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Extrafee\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Block\Conditions as BlockConditions;

class Condition extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var BlockConditions
     */
    protected $_blockConditions;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Fieldset $rendererFieldset,
        BlockConditions $blockConditions,
        array $data = []
    ){
        $this->_rendererFieldset = $rendererFieldset;
        $this->_blockConditions = $blockConditions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
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

        /** @var \Amasty\ShopbyPage\Model\Page $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEE);

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->_coreRegistry->registry(RegistryConstants::FEE_RULE);
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('amasty_extrafee/index/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Conditions (don\'t add conditions if need all products)')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'required' => true]
        )->setRule(
            $rule
        )->setRenderer(
            $this->_blockConditions
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}