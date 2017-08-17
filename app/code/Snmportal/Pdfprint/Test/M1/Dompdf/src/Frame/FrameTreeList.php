<?php
namespace Snmportal\External\Dompdf\Frame;

use IteratorAggregate;
use Snmportal\External\Dompdf\Frame;

/**
 * Pre-order IteratorAggregate
 *
 * @access private
 * @package dompdf
 */
class FrameTreeList implements IteratorAggregate
{
    /**
     * @var \Snmportal\External\Dompdf\Frame
     */
    protected $_root;

    /**
     * @param \Snmportal\External\Dompdf\Frame $root
     */
    public function __construct(Frame $root)
    {
        $this->_root = $root;
    }

    /**
     * @return FrameTreeIterator
     */
    public function getIterator()
    {
        return new FrameTreeIterator($this->_root);
    }
}
