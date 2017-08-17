<?php
namespace Snmportal\Pdfprint\Model\Pdf;

class Zpdf extends \Zend_Pdf
{
    protected $stream;
    public function setTCPFStream($stream)
    {
        $this->stream=$stream;
    }
    public function render($newSegmentOnly = false, $outputStream = null)
    {
        if ($outputStream === null) {
            return $this->stream;
        }
            $pdfData = $this->stream;
            while ( strlen($pdfData) > 0 && ($byteCount = fwrite($outputStream, $pdfData)) != false ) {
                $pdfData = substr($pdfData, $byteCount);
            }
            return '';

    }
}
