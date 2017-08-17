<?php
namespace  Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout;
/**
 * Adminhtml tier price item renderer
 */
use Magento\Backend\Block\Widget;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Columns extends Widget implements RendererInterface
{
    /**
     * Form element instance
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * Form element instance
     *
     * @var \Snmportal\Pdfprint\Model\Template
     */
    protected $_modelTemplate;

    /**
     * @var string
     */
    protected $_template = 'instance/edit/columns.phtml';
    //protected $_template = 'instance/edit/options.phtml';
    /**
     * Prepare global layout
     * Add "Add " button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Column'), 'onclick' => 'return '.$this->getJsName().'Control.addItem()', 'class' => 'add']
        );
        $button->setName('add_'.$this->getJsName().'_item_button');
        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }
    public function getReadOnly()
    {
        return false;
    }
    public function canManageOptionDefaultOnly()
    {
        return false;
    }

    public function getJsName()
    {
        if ( !$this->getData('js_name') )
        {
            $this->setData('js_name',uniqid('jsctrl_'));
        }
        return $this->getData('js_name');
    }
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }
        return $values;
    }

    public function setModelTemplate($template)
    {
        $this->_modelTemplate= $template;
        return $this;
    }
    public function getTypeOption()
    {

        $values = [];
        if ($this->_modelTemplate->isTemplate(\Snmportal\Pdfprint\Model\Template::TYPE_SHIPPING))
        {
            $values[] = array('label' => __('Position'), 'value' => 'position');
            $values[] = array('label' => __('Product Info'), 'value' => 'fr_product');
            $values[] = array('label' => __('Product Info and SKU'), 'value' => 'fr_product_sku');
            $values[] = array('label' => __('SKU'), 'value' => 'fr_sku');
            $values[]=array('label'=>__('Qty'),'value'=>'fr_qty');
            $values[]=array('label'=>__('Qty Number'),'value'=>'qty');
            $values[] = array('label' => __('Image'), 'value' => 'image');

        } else {
            $values[] = array('label' => __('Position'), 'value' => 'position');
            $values[] = array('label' => __('Product Info'), 'value' => 'fr_product');
            $values[] = array('label' => __('Product Info and SKU'), 'value' => 'fr_product_sku');
            $values[] = array('label' => __('SKU'), 'value' => 'fr_sku');
            $values[] = array('label' => __('Price'), 'value' => 'fr_price');
            $values[] = array('label' => __('Price Excl.Tax'), 'value' => 'price_excl_tax');
            $values[] = array('label' => __('Price Incl.Tax'), 'value' => 'price_incl_tax');
            if ($this->_modelTemplate->isTemplate(\Snmportal\Pdfprint\Model\Template::TYPE_ORDER)) {
                $values[] = array('label' => __('Original Price'), 'value' => 'price_original');

                $values[] = array('label' => __('Qty'), 'value' => 'fr_qty');
                $values[] = array('label' => __('Qty Ordered'), 'value' => 'qty_ordered');
                $values[] = array('label' => __('Qty Invoiced'), 'value' => 'qty_invoiced');
                $values[] = array('label' => __('Qty Shipped'), 'value' => 'qty_shipped');
                $values[] = array('label' => __('Qty Canceled'), 'value' => 'qty_canceled');
                $values[] = array('label' => __('Qty Refunded'), 'value' => 'qty_refunded');
            } else {
                $values[] = array('label' => __('Qty'), 'value' => 'fr_qty');
                $values[] = array('label' => __('Qty Number'), 'value' => 'qty');
            }

            //$values[]=array('label'=>__('Subtotal'),'value'=>'fr_subtotal');
            //$values[]=array('label'=>__('Subtotal Excl. Tax'),'value'=>'subtotal_excl_tax');
            $values[] = array('label' => __('Subtotal'), 'value' => 'subtotal_incl_tax');

            $values[] = array('label' => __('Tax Amount'), 'value' => 'tax_amount');
            $values[] = array('label' => __('Tax Percent'), 'value' => 'tax_percent');

            $values[] = array('label' => __('Discount Amount'), 'value' => 'discount_amount');

            $values[] = array('label' => __('Row Total'), 'value' => 'fr_row_total');
            $values[] = array('label' => __('Row Total Excl. Tax'), 'value' => 'row_total_excl_tax');
            $values[] = array('label' => __('Row Total Incl. Tax'), 'value' => 'row_total_incl_tax');


            $values[] = array('label' => __('Status'), 'value' => 'status');
            $values[] = array('label' => __('Image'), 'value' => 'image');
//        $values[]=array('label'=>__('Custom'),'value'=>'custom');
        }
        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        //usort($data, [$this, '_sortTierPrices']);
        return $data;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Render HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $this->setElement($element);
        return $this->toHtml();
    }


}
