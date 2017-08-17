<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Block;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_configs = [
        'is_mega_menu' => 1,
        'is_mobile_menu' => 0,
        'show_menu_title' => 0,
        'show_number_product' => 0,
        'mega_style' => 1,
        'default_mega_col_width' => 200,
        'mega_col_margin' => 20,
        'mega_content_visible_option' => null,
        'mega_content_visible_in' => null,
        'start_level' => 0,
        'end_level' => 10,
        'menu_group_id' => null,
        'menu_key' => null,
        'animation' => null,
        'addition_class' => null,
    ];

    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Mega
     */
    protected $_megaHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ubertheme\UbMegaMenu\Helper\Data $dataHelper,
        \Ubertheme\UbMegaMenu\Helper\Mega $megaHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_megaHelper = $megaHelper;

        //add needed assets
        $pageConfig = $context->getPageConfig();
        $pageConfig->addPageAsset('Ubertheme_UbMegaMenu::css/style.css');
        //font awesome
        $pageConfig->addPageAsset('Ubertheme_UbMegaMenu::css/font-awesome.min.css');
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        //initial configs
        $this->initialConfig($this->getData());

        //get menu group id
        if ($this->hasData('menu_id')) {
            $menuGroupId = $this->getData('menu_id');
            $menuGroup = $this->_dataHelper->getMenuGroupById($menuGroupId);
            $menuKey = $menuGroup->getIdentifier();
        } else {
            //get menu key from config
            $menuKey = ($this->hasData('menu_key')) ? trim($this->getData('menu_key')) : null;
            //get menu group id by menu key
            $menuGroup = $this->_dataHelper->getMenuGroupByKey($menuKey);
            $menuGroupId = $menuGroup->getId();
        }

        //update some other configs
        $this->_configs['menu_title'] = $menuGroup->getTitle();
        $this->_configs['menu_key'] = $menuKey;
        $this->_configs['menu_group_id'] = $menuGroupId;
        $this->_configs['animation'] = ($this->hasData('animation')) ? trim($this->getData('animation')) : $menuGroup->getAnimationType();

        //set config params for mega helper
        $this->_megaHelper->setParams($this->_configs);

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {

        //assign template
        if (!$this->getTemplate()) {
            $this->setTemplate("Ubertheme_UbMegaMenu::menu.phtml");
        }

        //get menu items and generate menu items tree html
        if ($this->_configs['menu_group_id'] AND $this->_configs['menu_key']) {
            $menuHtml = $this->_generateMenuHtml($this->_configs['menu_group_id']);
        } else {
            if ($this->_configs['menu_key']) {
                $menuHtml = '<div class="no-menu">' . __('Menu with key "%1" was not exists or it was disabled in this store.', $this->_configs['menu_key']) . '</div>';
            } else {
                $menuHtml = '<span class="no-menu">' . __('You have not set the menu to show in this store yet.') . '</span>';
            }
        }

        //assign data to template
        $this->assign('menuHtml', $menuHtml);
        $this->assign('config', $this->_configs);

        return $this->fetchView($this->getTemplateFile());
    }

    protected function _generateMenuHtml($menuGroupId)
    {
        //get menu items
        $items = $this->_dataHelper->getMenuItems($menuGroupId);
        if ($items) {
            //build menu items data
            $this->_megaHelper->rebuildData($items);
            //generate menu
            $output = $this->_megaHelper->genMenu();
        } else {
            $output = '<span class="no-menu">' . __('There are not menu items found.') . '</span>';
        }

        return $output;
    }

    protected function initialConfig($data)
    {
        foreach ($this->_configs as $key => $val) {
            $this->_configs[$key] = $this->_dataHelper->getConfigValue($key, $data);
        }

        return $this;
    }

    public function getIdentities()
    {
        return [
            \Magento\Store\Model\Store::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Group::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Item::CACHE_TAG
        ];
    }

}
