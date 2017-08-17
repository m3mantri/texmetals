<?php
namespace MageArray\OrderComment\Ui\Component\Listing\Column;

class Comment extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')
                    ->load($item['entity_id']);
                $comment = "There is no comment";
                if ($order->getOrderComment()) {
                    $comment = $order->getOrderComment();
                }
                $html = "<span>" . htmlspecialchars($comment) . "</span>";
                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}
