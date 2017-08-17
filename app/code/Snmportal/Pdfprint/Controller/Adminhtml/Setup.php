<?php
namespace Snmportal\Pdfprint\Controller\Adminhtml;

abstract class Setup extends \Magento\Backend\App\Action
{
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::setup');
    }



}
