<?php
namespace Snmportal\Pdfprint\Model\Pdf;
Autoloader::register();

class Renderer extends \Snmportal\External\Dompdf\Dompdf
{
    public $_extcanvas;
    public $_extoption;
    public $stringSubsetsText;

    public function __construct($options = null,$extcanavas=null)
    {
        $this->_extcanvas=$extcanavas;
        $this->_extoption=$options;
        $this->_globalcss ='';
        parent::__construct($options);
    }
    public function getStream()
    {
        return $this->output();
    }
    public function writeHTML($html,$css)
    {
        $canvas = $this->getCanvas();
        $renderer = new Renderer($this->_extoption,$canvas);
        $html= '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<style type="text/css">
@page { margin:0mm;  }
@page :first { margin:0mm;  }
@page :left { margin:0mm;  }
@page :right { margin:0mm;  }
@page :odd { margin:0mm;  }
@page :even { margin:0mm;  }
'.$css.'</style></head><body >'.$html.'</body></html>';
        $renderer->loadHtml($html);
        $renderer->render();
    }

    public function setCanvas(\Snmportal\External\Dompdf\Canvas $canvas)
    {
        if ( $this->_extcanvas )
        {
            parent::setCanvas($this->_extcanvas);
        }
        else
            parent::setCanvas($canvas);
        return $this;
    }

    static public function pt2mm($v)
    {
        return $v * 0.3528;
    }
    static public function mm2pt($v)
    {
        $v=(double)$v;
        $pt = $v / 0.3528;
        return $pt;
    }
    protected $_pdfTemplate;
    protected $_tplIdx;
    protected $_globalCSS='';
    protected $_styleInfos=array();
    protected $_caller;

    public function loadHtml($str, $encoding = null)
    {

        parent::loadHtml($str, $encoding);
    }

    public function handleRTLSupport()
    {
        foreach ($this->getTree()->get_frames() as $frame) {
            $style = $frame->get_style();
            $node = $frame->get_node();

            // Handle text nodes
            if ($node->nodeName === "#text") {
                if ($style->direction == 'rtl') {
                    $node->nodeValue = $this->checkRTL($node->nodeValue);
                }
                continue;
            }
        }
        if ( $this->stringSubsetsText )
        {
            foreach ( $this->stringSubsetsText as $font => $texts )
                foreach ( $texts as $text )
                    $this->getCanvas()->register_string_subset($font, $text);
        }
    }
    public function checkRTL($str)
    {
        if(  $str &&  preg_match('/\p{Arabic}/u', $str) ){
//            if ( !class_exists( 'I18N_Arabic' ) ) {
                require_once(__DIR__ . "/../../Test/M1/I18N/Arabic/Glyphs.php");
  //          }
            $Arabic = new \Snmportal\External\Arabic\I18N_Arabic_Glyphs('Glyphs');
            $str = $Arabic->utf8Glyphs($str);
        }
        return $str;
    }

}
