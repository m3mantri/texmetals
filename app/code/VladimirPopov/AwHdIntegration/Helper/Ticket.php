<?php

namespace VladimirPopov\AwHdIntegration\Helper;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Helpdesk\Model\Source\Ticket\Priority;
use Aheadworks\Helpdesk\Model\Source\Ticket\Status;

class Ticket extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    private $ticketFlatRepository;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    private $ticketDataFactory;

    /**
     * Ticket flat data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    private $ticketFlatDataFactory;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Thread message factory
     *
     * @var \Aheadworks\Helpdesk\Model\ThreadMessageFactory
     */
    private $threadMessageFactory;

    /**
     * Thread message resource
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage
     */
    private $threadMessageResource;

    /**
     * Result factory
     *
     * @var \VladimirPopov\WebForms\Model\ResultFactory
     */
    private $_resultFactory;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        \VladimirPopov\WebForms\Model\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CP\LimitOrders\Helper\WebForms $webformHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    )
    {
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->ticketDataFactory = $ticketInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->ticketFlatDataFactory = $ticketFlatInterfaceFactory;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        $this->_resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->_webformHelper = $webformHelper;
        $this->_productRepository = $productRepository;
        $this->_pricingHelper = $pricingHelper;

        parent::__construct($context);
    }

    public function createFromResult($result, $departmentId = 0){
        if(is_int($result)) {
            $result = $this->_resultFactory->create()->load($result);
        }

        $ticket = $this->ticketDataFactory->create();
        $ticketFlat = $this->ticketFlatDataFactory->create();

        //get form fields
        $limitOrder = array();
        $fieldName = array();
        $fields = $this->_webformHelper->getWebFormFields($result->getStoreId(), $result->getWebformId());
        foreach ($fields as $field) {
            $limitOrder[$field->getCode()] = trim($result->getData('field_' . $field->getId()));
            $fieldName[$field->getCode()] = $field->getId();
        }

        if(!isset($limitOrder['strike']) && !isset($limitOrder['order_id'])) {
            return;
        }

        // check available ticket for this result to prevent looping
        if(!empty($limitOrder['ticket_id'])) {
            $ticket = $this->ticketRepository->getById($limitOrder['ticket_id']);
            if($ticket->getId())
                return $ticket;
        }

        // get product
        $_product = $this->_productRepository->get($limitOrder['product']);

        if (isset($data['id'])) {
            unset($data['id']);
        }
        $customer_email = $result->getCustomerEmail();
        $data['customer_email'] = $customer_email[0];
        $data['priority'] = Priority::DEFAULT_VALUE;
        $data['status'] = Status::OPEN_VALUE;
        $data['agent_id'] = 0;
        // $data['subject'] = (string)$result->getEmailSubject();
        $data['subject'] = ($_product->getId()) ? $this->_pricingHelper->currency($limitOrder['strike'], true, false) . ' for ' . $_product->getSku() . ': ' . $_product->getName() : (string)$result->getEmailSubject();     // custom ticket title
        $data['content'] = $this->removeTags($result->toHtml());
        $data['department_id'] = $departmentId;
        $data['order_id'] = (isset($limitOrder['order_id'])) ? $limitOrder['order_id'] : null;
        $data['category'] = 'limitorder';
        $this->dataObjectHelper->populateWithArray(
            $ticket,
            $data,
            '\Aheadworks\Helpdesk\Api\Data\TicketInterface'
        );
        try {
            $ticket = $this->ticketRepository->save($ticket);
            //save message
            $data['ticket_id'] = $ticket->getId();
            $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE;
            $data['author_name'] = $result->getCustomerName();
            $data['author_email'] = $customer_email[0];
            if (isset($data['content']) && $data['content']) {
                $data['content'] = strip_tags($data['content']);
                $threadMessage = $this->threadMessageFactory->create()
                    ->setData($data);
                $this->threadMessageResource->save($threadMessage);
            }

            // $ticketFlat->setData('order_id', $ticket->getOrderId());
            $ticketFlat->setData('order_id', (isset($limitOrder['order_id'])) ? $limitOrder['order_id'] : $ticket->getOrderId());
            $ticketFlat->setData('agent_id', $ticket->getAgentId());
            $ticketFlat->setTicketId($ticket->getId());

            $this->ticketFlatRepository->save($ticketFlat);

            // set webform ticket id
            if(!isset($limitOrder['ticket_id']) || !$limitOrder['ticket_id']) {
                $result->setData('field', array($fieldName['ticket_id'] => $ticket->getId()) + $result->getData('field'));
                $result->save();
            }

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the ticket.'));
        }

        return $ticket;
    }

    public function removeTags($html)
    {
        $html = str_replace(array('<h2>','</h2>','<b>','</b>'),array('-',"-\n\n",'*','*'),$html);
        $html = preg_replace('/\<br(\s*)?\/?\>/i', "\n",$html);
        $html = strip_tags($html);
        return htmlspecialchars_decode($html);
    }
}