<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Controller\Adminhtml\Index;

/**
 * Class Save
 *
 * @author Artem Brunevski
 */

use Magento\Framework\Message\Error;
use Magento\Framework\Exception\LocalizedException;

class Save extends Index
{
    /**
     * @param array $data
     */
    protected function _prepareData(array &$data)
    {
        if (array_key_exists('rule', $data) && array_key_exists('conditions', $data['rule'])) {
            $data['conditions'] = $data['rule']['conditions'];

            unset($data['rule']);

            $salesRule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
            $salesRule->loadPost($data);

            $data['conditions_serialized'] = serialize($salesRule->getConditions()->asArray());
            unset($data['conditions']);
            unset($data['option']);
        }
    }
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Amasty\Extrafee\Model\CouldNotSaveException
     * @throws \Amasty\Extrafee\Model\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $feeId = null;
        $fee = $this->_feeRepository->create();

        if (array_key_exists('entity_id', $data)) {

            $feeId = $data['entity_id'];
            unset($data['entity_id']);

            if ($feeId) {
                $fee = $this->_feeRepository->getById($feeId);
            }
        }

        try {
            $options = array_key_exists('option', $data) ? $data['option'] : [];
            $this->_prepareData($data);
            $fee->addData($data);
            $this->_feeRepository->save($fee, $options);
            $feeId = $fee->getId();
            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $messages = $exception->getMessages();
            if (empty($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
            $returnToEdit = true;
        } catch (LocalizedException $exception) {
            $this->_addSessionErrorMessages($exception->getMessage());
            $returnToEdit = true;
        } catch (\Exception $exception) {
            $this->_addSessionErrorMessages( __('Something went wrong while saving the data.'));
            $returnToEdit = true;
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($feeId) {
                $resultRedirect->setPath(
                    'amasty_extrafee/*/edit',
                    ['id' => $feeId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'amasty_extrafee/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('amasty_extrafee/index');
        }
        return $resultRedirect;
    }

    /**
     * @param $messages
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }
}