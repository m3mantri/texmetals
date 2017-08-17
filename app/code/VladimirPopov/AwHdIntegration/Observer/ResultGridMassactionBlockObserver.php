<?php
namespace VladimirPopov\AwHdIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResultGridMassactionBlockObserver implements ObserverInterface
{

    protected $departmentsSource;

    public function __construct(
        \Aheadworks\Helpdesk\Model\Source\Departments $departmentsSource
    ) {
        $this->departmentsSource = $departmentsSource;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \VladimirPopov\WebForms\Block\Adminhtml\Result\Grid $grid */
        $grid = $observer->getGrid();

        $default = array('0' => __('Default'));
        $departments = $this->departmentsSource->toOptionArray();
        $department_options = $default + $departments;

        $grid->getMassactionBlock()->addItem('convert_to_tickets', array(
            'label'=> __('Convert to tickets'),
            'url'  => $grid->getUrl('awhdintegration/result/massConvertToTicket',array('webform_id'=>$grid->getRequest()->getParam('webform_id'))),
            'confirm' => __('Convert selected results to help desk tickets?'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'department_id',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Department'),
                    'values' => $department_options
                )
            )

        ));
    }
}