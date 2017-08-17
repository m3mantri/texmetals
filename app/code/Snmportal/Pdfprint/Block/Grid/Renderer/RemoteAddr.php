<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/
namespace Snmportal\Pdfprint\Block\Grid\Renderer;

class RemoteAddr extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return
            $row->getRemoteAddr() .
            ' (' .long2ip($row->getRemoteAddr() ).')'
            ;
    }
}
