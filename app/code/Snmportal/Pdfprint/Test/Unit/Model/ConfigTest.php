<?php
namespace Snmportal\Pdfprint\Test\Unit\Model;

/**
 * Class Config
 *
 */
class Config extends \PHPUnit_Framework_TestCase
{
    const TEST_TYPINFO = 'order';
    /**
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    private $template;

    public function setUp()
    {
        parent::setUp();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->attachment = $objectManager->getObject('Snmportal\Pdfprint\Helper\Template');

    }

    public function testTypinfo()
    {
        $this->assertEquals(self::TEST_TYPINFO,
            $this->template->getTypinfo(\Snmportal\Pdfprint\Model\Template::TYPE_ORDER)[0]);
    }

}
