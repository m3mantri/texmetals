<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Button;
use Magento\Customer\Model\Context;

class ShipmentButton extends AbstractButton
{
    protected function _toHtml()
    {
        if ( !$this->_scopeConfig->isSetFlag('snmportal_pdfprint/general/enabled')
            || !$this->_scopeConfig->isSetFlag('snmportal_pdfprint/shipment/enabled')
            || !$this->_scopeConfig->isSetFlag('snmportal_pdfprint/shipment/use_pdf_frontend')) {
            return '';
        }
        return $this->_getDefaultCss();
    }
    public function _getDefaultCss()
    {
        $style='<style>';
        $style .= 'body .order-details-items .action.print{display:block;}';
        $style.='</style>';
        return $style;
    }


}

