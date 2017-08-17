<?php
namespace Snmportal\Pdfprint\Controller\Adminhtml;

abstract class Template extends \Magento\Backend\App\Action
{
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template');
    }


    /**
     * Init widget instance object and set it to registry
     *
     * @return \Magento\Widget\Model\Widget\Instance|boolean
     */
    protected function _initModelInstance()
    {
        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');
        $id = $this->getRequest()->getParam('template_id');
       // $type = $this->getRequest()->getParam('type', null);
        if ($id) {
            $model ->load($id);
            if (!$model ->getId()) {
                $this->messageManager->addError(__('Please specify a correct template.'));
                return false;
            }
        }
        $this->_coreRegistry->register('snmportal_pdfprint_template', $model);
        return $model;
    }

}
