<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Block\Adminhtml\Fee\Edit\Tab;

/**
 * Class Filter
 *
 * @author Artem Brunevski
 */

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Extrafee\Controller\RegistryConstants;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Customer\Ui\Component\Listing\Column\Group\Options as GroupOptions;

class Filter extends Generic implements TabInterface
{
    /** @var SystemStore  */
    protected $_systemStore;

    /** @var GroupOptions  */
    protected $_groupOptions;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SystemStore $systemStore
     * @param GroupOptions $groupOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SystemStore $systemStore,
        GroupOptions $groupOptions,
        array $data = []
    ){
        $this->_systemStore = $systemStore;
        $this->_groupOptions = $groupOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Stores & Customer Groups');
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

        $form->setHtmlIdPrefix('amasty_extrafee_');

        /** @var \Amasty\ShopbyPage\Model\Page $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEE);

        $fieldset = $form->addFieldset(
            'filter_fieldset',
            ['legend' => __('Stores & Customer Groups'), 'class' => 'fieldset-wide']
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                ]
            );

            /** @var \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element $renderer */
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField('customer_group_id', 'multiselect', array(
            'label' => __('Customer Groups'),
            'title' => __('Customer Groups'),
            'name' => 'groups[]',
            'required' => true,
            'values' => $this->_groupOptions->toOptionArray(),
        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}