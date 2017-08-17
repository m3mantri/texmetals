<?php
/**
 * Created by PhpStorm.
 * User: mau
 * Date: 14.04.2016
 * Time: 09:10
 */

namespace Snmportal\Pdfprint\Model\Pdf;

use Magento\Framework;
//use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View;

class ResultView extends \Magento\Framework\View\Result\Page
{

    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        $template,
        $isIsolated = false
    )
    {

        parent::__construct($context,
            $layoutFactory,
        $layoutReaderPool,
        $translateInline,
        $layoutBuilderFactory,
        $generatorPool,
        $pageConfigRendererFactory,
        $pageLayoutReader,
        $template,false);

        // Neues Layout setzen ansonsten funtioniert Umschaltung backend/frontend nicht
        //$this->layout = $layout;
        $this->layout = $layoutFactory->create(['reader' => $layoutReaderPool, 'generatorPool' => $generatorPool]);
        $this->layout->setGeneratorPool($generatorPool);
        $this->initLayoutBuilder();

//        $this->layout = $this->layoutFactory->create(['reader' => $this->layoutReaderPool, 'generatorPool' => $generatorPool]);
  //      $this->layout->setGeneratorPool($generatorPool);

    }

}