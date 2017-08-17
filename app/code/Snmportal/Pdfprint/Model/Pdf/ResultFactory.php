<?php
namespace Snmportal\Pdfprint\Model\Pdf;
use Magento\Framework\ObjectManagerInterface;
class ResultFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = 'Snmportal\Pdfprint\Model\Pdf\ResultView'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create new page regarding its type
     *
     * TODO: As argument has to be controller action interface, temporary solution until controller output models
     * TODO: are not implemented
     *
     * @param bool $isView
     * @param array $arguments
     * @return \Magento\Framework\View\Result\Page
     */
    public function create($isView = false, array $arguments = [])
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $this->objectManager->create($this->instanceName, $arguments);
        // TODO Temporary solution for compatibility with View object. Will be deleted in MAGETWO-28359
        if (!$isView) {
            $page->addDefaultHandle();
        }
        return $page;
    }
}
