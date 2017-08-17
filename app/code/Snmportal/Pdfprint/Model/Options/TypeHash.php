<?php
namespace Snmportal\Pdfprint\Model\Options;
use \Snmportal\Pdfprint\Model\Template;
class TypeHash implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * System Store Model
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(\Magento\Store\Model\System\Store $systemStore)
    {
        $this->_systemStore = $systemStore;
    }

    /**
     * Return store group array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [array('value'=>Template::TYPE_ORDER,'label' => __('Order')),
            array('value'=>Template::TYPE_INVOICE,'label' => __('Invoice')),
            array('value'=>Template::TYPE_SHIPPING,'label' => __('Shipping')),
            array('value'=>Template::TYPE_CREDITMEMO,'label' => __('Credit Memo'))
        ];
    }
    public function toHashArray()
    {
        return [Template::TYPE_ORDER => __('Order'),
            Template::TYPE_INVOICE => __('Invoice'),
            Template::TYPE_SHIPPING => __('Shipping'),
            Template::TYPE_CREDITMEMO => __('Credit Memo')
        ];
    }
}
