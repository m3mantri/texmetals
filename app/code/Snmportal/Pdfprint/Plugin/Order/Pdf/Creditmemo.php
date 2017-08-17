<?php
namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Creditmemo as MCreditmemo;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;
use Snmportal\Pdfprint\Model\Template;

class Creditmemo extends AbstractPlugin
{
    public function aroundGetPdf(
        MCreditmemo $subject,
        \Closure $proceed,
        $documents = []
    ) {
        $engine = $this->getEngine(Template::TYPE_CREDITMEMO,null);
        if ($engine )
            return $engine->getPdf($documents);
        return $proceed($documents);
    }

}
