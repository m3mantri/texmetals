<?php
namespace Snmportal\Pdfprint\Plugin\Framework;

class Translate
{
    public function aroundLoadData(
        \Magento\Framework\Translate $subject,
        $proceed,
        $area,
        $forceReload)
    {
        $returnValue = $proceed();


        return $returnValue;
    }

    public function afterLoadData(
        \Magento\Framework\Translate $subject,        $result  ){

        return $result;
    }
}
