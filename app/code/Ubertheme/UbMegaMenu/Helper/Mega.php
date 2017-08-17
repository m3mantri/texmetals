<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Ubertheme\UbMegaMenu\Model\Item\Image as ImageModel;

/**
 * Mega Helper
 *
 */
class Mega extends AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    protected $children = [];

    protected $items = [];

    protected $active_tree = [];

    protected $param = null;

    protected $menuHtml = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ImageModel $imageModel
    )
    {
        $this->_storeManager = $storeManager;
        $this->imageModel = $imageModel;

        //initial mega param values
        $this->initParam();

        parent::__construct($context);
    }

    public function initParam()
    {
        $this->param = new \stdClass();
    }

    public function setParams($params = [])
    {
        if ($params) {
            foreach ($params as $key => $value) {
                $this->param->$key = $value;
            }
        }
    }

    public function getParam($key = null)
    {
        $rs = null;
        if ($key) {
            if (isset($this->param->$key)) {
                $rs = $this->param->$key;
            }
        } else {
            $rs = $this->param;
        }

        return $rs;
    }

    /**
     * Rebuild menu items data
     *
     * @param $items
     * @return bool
     */
    public function rebuildData($items)
    {

        //get mobile detect object
        $detect = \Magento\Framework\App\ObjectManager::getInstance()->create('Ubertheme\UbMegaMenu\Helper\MobileDetect');
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');

        //for developers only
        $device = $this->getRequest()->getParam('device', null);
        if ($device AND in_array($device, ['mobile', 'tablet', 'desktop'])) {
            $deviceType = $device;
        }

        //built tree ids of each item
        $temp = array();
        foreach ($items as $item) {
            $child = [];
            if (isset($temp[$item->getParentId()])) {
                $child = $temp[$item->getParentId()];
            }
            array_push($child, $item->getId());
            $temp[$item->getId()] = $child;
            $item->setData('tree', $child);
        }

        //get current active item
        $activeItem = $this->_getActiveItem($items);
        if ($activeItem) {
            //update active tree
            $this->active_tree = $activeItem->getData('tree');
        }

        @$children = [];
        foreach ($items as $item) {

            $pt = (int)$item->getParentId();
            $list = isset($children[$pt]) ? $children[$pt] : [];

            //build some mega params
            $megaParam = new \stdClass();
            $megaParam->device = $deviceType;
            $megaParam->cols = $item->getMegaCols() ? $item->getMegaCols() : 1;
            $megaParam->is_group = $item->isGroup();
            $megaParam->class = $item->getAdditionClass();
            $megaParam->width = $item->getMegaWidth();
            $megaParam->col_width = $item->getMegaColWidth(); // width of a column
            $megaParam->visible_in = $this->getVisibleIn($item);
            $megaParam->sub_content_type = $item->getMegaSubContentType();
            $megaParam->desc = '';

            //get mega col_x width
            /**
             * Example format:
             * col1=50
             * col2=100
             * col3=50
             */
            if (preg_match_all('/([^\s]+)=([^\s]+)/', $item->getMegaColXWidth(), $colwMatches)) {
                for ($i = 0; $i < count($colwMatches[0]); $i++) {
                    $attrCol = (string)$colwMatches[1][$i];
                    $megaParam->$attrCol = $colwMatches[2][$i];
                }
            }

            $item->setData('mega_param', $megaParam);

            //only show mega contents (desc, Custom content, Static Blocks of Menu item) as settings
            if (in_array($deviceType, $megaParam->visible_in)) {

                $megaParam->desc = $item->getDescription();

                $blocks = $this->loadBlocks($item);
                if (is_array($blocks) AND count($blocks) > 0) {
                    $content = '';
                    $item->setData('content', $content);

                    $total = count($blocks);
                    $cols = min($item->getData('mega_param')->cols, $total);
                    for ($col = 0; $col < $cols; $col++) {
                        $pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
                        if ($cols > 1)
                            $content .= $this->beginSubMenuContent($item, 1, $pos, $col, true);
                        $i = $col;
                        while ($i < $total) {
                            $block = $blocks[$i];
                            $i += $cols;
                            $content .= $block->toHtml();
                        }
                        if ($cols > 1)
                            $content .= $this->endSubMenuContent($item, 1, true);
                    }
                    $item->setData('cols', $cols);
                    $item->setData('content', trim($content));

                    $this->items[$item->getId()] = $item;
                } elseif ($blocks) { //custom content html
                    $item->setData('content', $blocks);
                }
            }

            if ($megaParam->cols) {
                $item->setData('cols', $megaParam->cols);
                for ($i = 1; $i <= $megaParam->cols; $i++) {
                    if (isset($megaParam->{"col{$i}"}) AND $megaParam->{"col{$i}"}) {
                        $item->setData("col{$i}", $megaParam->{"col{$i}"});
                    }
                }
            }

            $item->setData('_idx', count($list));
            array_push($list, $item);
            $children[$pt] = $list;

            $this->items[$item->getId()] = $item;
        }
        $this->children = $children;

        return true;
    }

    /**
     * Generate menu html
     *
     * @param int $pid
     * @param int $level
     * @return string
     */
    public function genMenu($pid = 0, $level = 0)
    {
        if (!isset($this->children[$pid])) {
            return '';
        }
        $this->beginMenu();
        $this->genMenuItems($pid, $level);
        $this->endMenu();

        return $this->menuHtml;
    }

    public function beginMenu()
    {
        $animation = $this->param->animation;
        $menuGroupId = $this->param->menu_group_id;
        if ($this->param->addition_class) {
            $additionClass = "{$this->param->addition_class} ";
        } else {
            $additionClass = "";
        }

        $this->menuHtml = "<div id='ub-mega-menu-{$menuGroupId}' class='{$additionClass}ub-mega-menu-wrapper'>\n";

        if ($this->param->show_menu_title) {
            $this->menuHtml .= '<h3 class="ub-mega-menu-title">' . $this->param->menu_title . '</h3>';
        }

        $this->menuHtml .= "<div class=\"{$animation} ub-mega-menu clearfix\" >\n";
    }

    public function genMenuItems($pid, $level)
    {
        if (isset($this->children[$pid]) AND $this->children[$pid]) {
            $cols = ($pid AND $this->items[$pid]->getData('cols')) ? $this->items[$pid]->getData('cols') : 1;
            $total = count($this->children[$pid]);

            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $tmp = isset($this->items[$pid]) ? $this->items[$pid] : $om->create('\Ubertheme\UbMegaMenu\Model\Item');

            // calculate number of menu items per column
            if ($cols > 1) {
                $fixItems = 0;
                if ($fixItems < $cols) {
                    $leftItem = $total - $fixItems;
                    $col = [];
                    $items = ceil($leftItem / ($cols - $fixItems));
                    for ($m = 0; $m < $cols AND $leftItem > 0; $m++) {
                        $colTmp = $tmp->getData('col');
                        if (!isset($colTmp[$m]) || !$colTmp[$m]) {
                            if ($leftItem > $items) {
                                $col[$m] = $items;
                                $leftItem -= $items;
                            } else {
                                $col[$m] = $leftItem;
                                $leftItem = 0;
                            }
                        }
                    }
                    $tmp->setData('col', $col);
                    $cols = count($col);
                    $tmp->setData('cols', $cols);
                }
            } else {
                $tmp->setData('col', [$total]);
            }

            //recalculate the colw for this column if the first child is group
            for ($col = 0, $j = 0; $col < $cols AND $j < $total; $col++) {
                $i = 0;
                $colw = 0;
                $colTmp = $tmp->getData('col');
                while ($i < $colTmp[$col] AND $j < $total) {
                    $row = $this->children[$pid][$j];
                    if ($row->getData('mega_param')->is_group AND $row->getData('mega_param')->width > $colw) {
                        $colw = $row->getData('mega_param')->width;
                    }
                    $j++;
                    $i++;
                }
                if ($colw) {
                    if (isset($this->items[$pid])) {
                        $this->items[$pid]->getData('mega_param')->{'col' . ($col + 1)} = $colw;
                    }
                }
            }

            $this->beginMenuItems($pid, $level);

            for ($col = 0, $j = 0; $col < $cols AND $j < $total; $col++) {
                $pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
                //recalculate the colw for this column if the first child is group
                if ($this->children[$pid][$j]->getData('mega_param')->is_group AND $this->children[$pid][$j]->getData('mega_param')->width) {
                    $this->items[$pid]->getData('mega_param')->{'col' . ($col + 1)} = $this->children[$pid][$j]->getData('mega_param')->width;
                }

                $this->beginSubMenuItems($pid, $level, $pos, $col);

                $i = 0;
                $colTmp = $tmp->getData('col');
                while ($i < $colTmp[$col] AND $j < $total) {
                    $row = $this->children[$pid][$j];
                    $pos = ($i == 0) ? 'first' : (($i == count($this->children[$pid]) - 1) ? 'last' : '');

                    $this->beginMenuItem($row, $level, $pos);
                    $this->genMenuItem($row, $level, $pos);

                    // show menu with menu expanded - sub-menus visible
//                    if (isset($row->getData('mega_param')->is_group) AND $row->getData('mega_param')->is_group) {
//                        $this->genMenuItems($row->getId(), $level); //not increase level
//                    } elseif ($level < $this->param->end_level) {
//                        $this->genMenuItems($row->getId(), $level + 1);
//                    }
                    if ($level < $this->param->end_level) {
                        $this->genMenuItems($row->getId(), $level + 1);
                    }

                    $this->endMenuItem($row, $level, $pos);
                    $j++;
                    $i++;
                }

                $this->endSubMenuItems($pid, $level);
            }

            $this->endMenuItems($pid, $level);
        }
    }

    public function endMenu()
    {
        $this->menuHtml .= "\n</div>";
        $this->menuHtml .= "\n</div>";
    }

    public function beginMenuItems($pid = 0, $level = 0, $return = false)
    {
        $data = '';
        if ($level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            if ($megaParam->is_group) {
                $cols = ($pid AND isset($megaParam->cols) AND $megaParam->cols) ? $megaParam->cols : 1;
                $cols_cls = ($cols > 1) ? " cols$cols" : '';
                $data = "<div class=\"group-content$cols_cls\">";
            } else {
                $style = $this->param->mega_style;
                $data = call_user_func_array(array($this, "beginMenuItems$style"), array($pid, $level, true));
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function beginMenuItems1($pid = 0, $level = 0, $return = false)
    {
        $megaParam = $this->items[$pid]->getData('mega_param');
        $cols = ($pid AND isset($megaParam->cols) AND $megaParam->cols) ? $megaParam->cols : 1;
        $width = $megaParam->width;
        if (!$width) {
            for ($col = 0; $col < $cols; $col++) {
                $colxName = 'col' . ($col + 1);
                $colw = property_exists($megaParam, $colxName) ? $megaParam->$colxName : null;
                if (!$colw) {
                    $colw = ($megaParam->col_width) ? $megaParam->col_width : $this->param->default_mega_col_width;
                }
                //update width of container
                $width += $colw + $this->param->mega_col_margin;
            }
        }
        $style = $width ? " style=\"width: {$width}px;\"" : "";
        $right = isset($this->items[$pid]->getData('mega_param')->right) ? 'right' : '';
        $data = "<div class=\"child-content cols{$cols} {$right}\">\n";
        $data .= "<div class=\"child-content-inner-wrap\" id=\"child-content-{$pid}\">\n"; //add wrapper
        $data .= "<div class=\"child-content-inner clearfix\"{$style}>"; //move width into inner

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endMenuItems1($pid = 0, $level = 0, $return = false)
    {
        $data = "</div>\n"; //close of child-content-inner
        $data .= "</div></div>"; //close wrapper and child-content
        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endMenuItems($pid = 0, $level = 0, $return = false)
    {
        if ($level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            if ($megaParam->is_group) {
                $data = "</div>";
            } else {
                $style = $this->param->mega_style;
                $data = call_user_func_array(array($this, "endMenuItems$style"), array($pid, $level, true));
            }
            if ($return) {
                return $data;
            } else {
                $this->menuHtml .= $data;
            }
        }
    }

    public function beginSubMenuContent($item, $level = 0, $pos, $i, $return = false)
    {
        $data = '';
        $megaParam = $item->getData('mega_param');
        if ($level) {
            if (!$megaParam->is_group) {
                $colxName = 'col' . ($i + 1);
                $colw = (isset($megaParam->$colxName)) ? $megaParam->$colxName : null;
                if (!$colw) {
                    $colw = $this->items[$item->getParentId()]->getData('mega_param')->col_width ? $this->items[$item->getParentId()]->getData('mega_param')->col_width : $this->param->default_mega_col_width;
                }
                $style = $colw ? " style=\"width: {$colw}px;\"" : "";
                $data .= "<div class=\"mega-col column" . ($i + 1) . ($pos ? " $pos" : "") . "\"$style>";
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endSubMenuContent($item, $level = 0, $return = false)
    {
        $data = '';
        if ($level) {
            if (!$item->getData('mega_param')->is_group) {
                $data .= "</div>";
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function beginSubMenuItems($pid = 0, $level = 0, $pos, $i, $return = false)
    {
        $level = (int)$level;
        $data = '';
        if (isset($this->items[$pid]) AND $level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            $cols = ($pid AND isset($megaParam->cols) AND $megaParam->cols) ? $megaParam->cols : 1;
            if ($megaParam->is_group AND $cols < 2) {
                //coming soon
            } else {
                $colw = 0;
                $colxName = 'col' . ($i + 1);
                if (isset($megaParam->$colxName))
                    $colw = $megaParam->$colxName;
                if (!$colw) {
                    $colw = $megaParam->col_width ? $megaParam->col_width : $this->param->default_mega_col_width;
                }
                $style = $colw ? " style=\"width: {$colw}px;\"" : "";
                $data .= "<div class=\"mega-col column" . ($i + 1) . ($pos ? " $pos" : "") . "\"$style>";
            }
        }

        if (@$this->children[$pid]) {
            if ($this->param->is_mobile_menu) {
                if ($level == 0) {
                    $data .= "<ul data-mage-init='{\"menu\":{\"responsive\":false, \"expanded\":false, \"position\":{\"my\":\"left top\",\"at\":\"left bottom\"}}}' class=\"mega-menu level{$level}\">";
                } else {
                    $data .= "<ul class=\"mega-menu level{$level} mega-sub-menu\">";
                }
            } else {
                $data .= "<ul class=\"mega-menu level{$level}\">";
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endSubMenuItems($pid = 0, $level = 0, $return = false)
    {
        $data = '';
        if (@$this->children[$pid]) {
            $data .= "</ul>";
        }
        if (isset($this->items[$pid]) AND $level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            $cols = ($pid AND isset($megaParam->cols) AND $megaParam->cols) ? $megaParam->cols : 1;
            if ($megaParam->is_group AND $cols < 2) {
                //coming soon
            } else {
                $data .= "</div>";
            }
        }
        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function beginMenuItem($item = null, $level = 0, $pos = '')
    {
        $class = $this->genClass($item, $level, $pos);
        if ($class) {
            $class = " class=\"{$class}\"";
        }
        $this->menuHtml .= "<li {$class}>";

        if ($item->getData('mega_param')->is_group) {
            $this->menuHtml .= "<div class=\"group\">";
        }
    }

    public function endMenuItem($item = null, $level = 0, $pos = '')
    {
        if ($item->getData('mega_param')->is_group)
            $this->menuHtml .= "</div>";
        $this->menuHtml .= "</li>";
    }

    /**
     * @param $item
     * @param int $level
     * @param string $pos
     * @param int $return
     * @return string
     */
    public function genMenuItem($item, $level = 0, $pos = '', $return = 0)
    {
        /* @var \Ubertheme\UbMegaMenu\Model\Item $item */
        $data = '';
        $id = 'id="menu' . $item->getId() . '"';
        $itemTitle = ($this->param->is_mega_menu AND !$item->isShowTitle()) ? '' : $item->getTitle();

        $class = $this->genClass($item, $level, $pos);
        if ($class) {
            $class = " class=\"$class\"";
        }

        //check show number products in menu title
        if ($item->getLinkType() == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
            /* @var \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository*/
            $categoryRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Api\CategoryRepositoryInterface');
            /* @var \Magento\Catalog\Api\Data\CategoryInterface $category */
            $category = $categoryRepository->get($item->getCategoryId(), $this->_storeManager->getStore()->getId());
            if ($category) {
                //update menu item link by store
                $item->setLink($category->getUrl());
                if ($itemTitle AND $this->isShowNumberProduct($item)) {
                    //append number products to title
                    $itemTitle .= '<span class="number-product"> (' . $category->getProductCount() . ')</span>';
                }
            }
        }

        //add font awesome
        if ($item->getFontAwesome()) {
            $itemTitle = '<i class="fa ' . $item->getFontAwesome() . '"></i>' . $itemTitle;
        }

        //build menu item title
        if ($itemTitle) {
            $txt = '<span class="menu-title">' . $itemTitle . '</span>';
        } else {
            $txt = '';
        }

        //add icon image
        if ($item->getIconImage()) {
            $icon = '<span class="menu-icon"><img alt="' . strip_tags($itemTitle) . '" src="' . $this->imageModel->getBaseUrl() . $item->getIconImage() . '" /></span> ';
            $txt = $icon . $txt;
        }

        //menu link (url) process
        $link = $item->getLink();
        $title = strip_tags("title=\"{$itemTitle}\"");

        if ($txt) {
            if ($link) {
                switch ($item->getLinkTarget()) {
                    default:
                    case '_top':
                        $data = '<a href="' . $link . '" ' . $class . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
                        break;
                    case '_blank':
                        $data = '<a href="' . $link . '" target="_blank" ' . $class . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
                        break;
                    case '_parent':
                        $data = '<a href="' . $link . '" target="_parent" ' . $class . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
                        break;
                }
            } else {
                $data = '<a ' . $class . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
            }

            //add description to menu item
            $megaParam = $item->getData('mega_param');
            if (isset($megaParam->desc) AND $megaParam->desc) {
                $desc = \Magento\Framework\App\ObjectManager::getInstance()->create('Ubertheme\UbMegaMenu\Model\Processor')->filter($megaParam->desc);
                $data .= '<div class="menu-desc">' . $desc . '</div>';
            }

            //append menu parent icon
            $hasChildClass = substr_count($class, 'has-child');
            if ($hasChildClass) {
                $data .= '<span class="menu-parent-icon"></span>';
            }
        }

        if ($this->param->is_mega_menu) {
            if (($item->getData('mega_param')->is_group) AND $data) {
                $data = "<div class=\"group-title\">{$data}</div>";
            }
            if ($item->getData('content')) {
                if ($item->getData('mega_param')->is_group) {
                    $data .= "<div class=\"group-content\">{$item->getData('content')}</div>";
                } else {
                    $data .= $this->beginMenuItems($item->getId(), $level + 1, true);
                    $data .= $item->getData('content');
                    $data .= $this->endMenuItems($item->getId(), $level + 1, true);
                }
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function loadBlocks($item)
    {
        $blocks = array();
        $subContentType = $item->getMegaSubContentType();
        switch ($subContentType) {
            case 'static-block':
                $ids = $item->getStaticBlocks();
                $ids = preg_split('/,/', $ids);
                foreach ($ids as $id) {
                    if ($id) {
                        $storeId = $this->_storeManager->getStore()->getId();
                        $block = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Cms\Model\Block')->setStoreId($storeId)->load($id);
                        //$view =  \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\View');
                        $layout = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\View\LayoutInterface');
                        $block = $layout->createBlock('Magento\Cms\Block\Block')->setBlockId($block->getIdentifier());
                        $blocks[] = $block;
                    }
                }
                return $blocks;
            case 'custom-content':
                $block = \Magento\Framework\App\ObjectManager::getInstance()->create('Ubertheme\UbMegaMenu\Model\Processor')->filter($item->getCustomContent());
                return $block;
            default:
                return null;
        }
    }

    /**
     * @param $item
     * @param $level
     * @param $pos
     * @return string
     */
    public function genClass($item, $level, $pos)
    {
        $megaParam = $item->getData('mega_param');
        $cls = "mega" . ($pos ? " $pos" : "");
        if (@$this->children[$item->getId()] || ($item->hasData('content') AND $item->getData('content'))) {
            if ($megaParam->is_group) {
                $cls .= " group";
            } elseif ($level < $this->param->end_level) {
                $cls .= " has-child";
            }
        }

        //check current active and add active class
        if (isset($this->active_tree)) {
            $active = in_array($item->getId(), $this->active_tree);
        } else {
            $active = false;
        }
//        if (!preg_match('/group/', $cls)) {
//            $cls .= ($active ? " active" : "");
//        }
        $cls .= ($active ? " active" : "");

        //add addition class to menu item
        if ($megaParam->class) {
            $cls .= " " . $megaParam->class;
        }

        return $cls;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isShowNumberProduct($item)
    {
        $globalConfig = $this->getParam('show_number_product');
        if ($item->isShowNumberProduct() == \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_YES
            OR ($item->isShowNumberProduct() == \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG AND $globalConfig)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $item
     * @return array|null
     */
    protected function getVisibleIn($item)
    {
        $globalConfig = $this->getParam('mega_content_visible_in');
        if ($item->getVisibleOption() == \Ubertheme\UbMegaMenu\Model\Item::VISIBLE_OPTION_USE_GENERAL_CONFIG AND $globalConfig) {
            $rs = $globalConfig;
        } else {
            $rs = $item->getVisibleIn();
        }

        if ($rs) { //format: desktop,tablet,mobile
            $rs = explode(',', $rs);
        } else {
            $rs = [];
        }

        return $rs;
    }

    /**
     * @param $items
     * @return null
     */
    private function _getActiveItem($items)
    {
        $activeItem = null;
        //get current url
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $currentUrl = $urlInterface->getCurrentUrl();
        foreach ($items as $item) {
            if ($item->getLinkType() == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
                /* @var \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository*/
                $categoryRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Api\CategoryRepositoryInterface');
                /* @var \Magento\Catalog\Api\Data\CategoryInterface $category */
                $category = $categoryRepository->get($item->getCategoryId(), $this->_storeManager->getStore()->getId());
                if ($category) {
                    //update menu item link by store
                    $item->setLink($category->getUrl());
                }
            }
            $link = $item->getLink();
            if ($link AND $link != '#') {
                if (!preg_match('#^(http|https)://.+(\.[a-z]{2,5})$#', $link)) { // is not full link
                    $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
                    $findBaseUrlMark = strpos($link, "{base_url}");
                    if (($item->getLinkType() == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CUSTOM) AND $findBaseUrlMark >= 0) {
                        $link = str_replace('{base_url}', $baseUrl, $link);
                    } else {
                        $link = $baseUrl . $link;
                    }
                    //update full link
                    $item->setLink($link);
                }
            }

            if ($currentUrl == $link) {
                $activeItem = $item;
            }
        }

        return $activeItem;
    }

    /**
     * @return mixed
     */
    protected function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');
        return $context->getRequest();
    }
}
