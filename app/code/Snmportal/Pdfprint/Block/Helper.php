<?php
// @codingStandardsIgnoreFile

namespace Snmportal\Pdfprint\Block;

class Helper extends \Magento\Framework\View\Element\AbstractBlock
{
    protected $_evalTemplate;
    public function exceptionHandler($ex)
    {
        //@todo Block Ausgabe

        $msg = "Fatal error (pdfPRINT) eval failed:\n".$ex->getMessage();
        if ( $this->_evalTemplate && is_array($this->_evalTemplate) )
        {
            $msg .= "\n\nTemplate\nName:".$this->_evalTemplate['name'];
            $msg .= "\nSource:\n".$this->_evalTemplate['value'];
        }
        error_log("\n".$msg, 3, 'var/log/snm-portal.log');
//        echo str_replace("\n","<br/>",htmlentities($msg));
  //      exit();
    //    */
        return false;
    }
    public function render($template)
    {
        //@todo Block Ausgabe

        ob_start();
        try {
            $this->_evalTemplate = $template;
            extract($this->getTemplateFilter()->getVariables(), EXTR_SKIP);
//            $last = set_exception_handler([$this,'exceptionHandler']);
            $tmpphp = 'var/tmp/'.uniqid('snmpdf', true).'.php';
            file_put_contents($tmpphp,$template['value']);
            include $tmpphp;
            @unlink($tmpphp);
//          set_exception_handler($last);
        } catch (\Exception $exception) {
            ob_end_clean();
            return $exception->getMessage();
        }
        /** Get output buffer. */
        $output = ob_get_clean();
        return $output;
    }

    protected function _toHtml()
    {
        if (!$this->getBlockId()) {
            return '';
        }
        $blockId = $this->getBlockId();
        //@todo Block Ausgabe
        //return __('Block template not found: %1',$blockId);

        if ( !$this->getTemplateFilter() || !$this->getTemplateFilter()->auitVariable('templateinfo')
             || $this->getTemplateFilter()->auitVariable('templateinfo')->getBlockTemplate($blockId) === false )
            return __('Block template not found: %1',$blockId);

        $blockTemplate = $this->getTemplateFilter()->auitVariable('templateinfo')->getBlockTemplate($blockId);
        return $this->render($blockTemplate);
    }
}
