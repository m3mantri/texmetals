<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
namespace Snmportal\External\Dompdf\FrameDecorator;

use Snmportal\External\Dompdf\Dompdf;
use Snmportal\External\Dompdf\Frame;

/**
 * Dummy decorator
 *
 * @package dompdf
 */
class NullFrameDecorator extends AbstractFrameDecorator
{

    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $style = $this->_frame->get_style();
        $style->width = 0;
        $style->height = 0;
        $style->margin = 0;
        $style->padding = 0;
    }

}
