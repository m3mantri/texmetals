<?php

namespace VladimirPopov\AwHdIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use VladimirPopov\WebForms\Model;

class WebformsResultSaveObserver implements ObserverInterface
{

    /** @var  Model\FormFactory */
    protected $formFactory;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * Ticket Helper
     *
     * @var \VladimirPopov\AwHdIntegration\Helper\Ticket
     */
    private $ticketHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Model\FormFactory $formFactory,
        \VladimirPopov\AwHdIntegration\Helper\Ticket $ticketHelper
    )
    {
        $this->logger = $logger;
        $this->formFactory = $formFactory;
        $this->ticketHelper = $ticketHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $observer->getResult();
        $form = $this->formFactory->create()->load($result->getWebformId());
        $create_tickets = $form->getData('awheldesk_create_tickets');
        if ($create_tickets) {
            $departmentId = $form->getData('awhelpdesk_default_department');
            $this->ticketHelper->createFromResult($result, $departmentId);
        }
    }

}
