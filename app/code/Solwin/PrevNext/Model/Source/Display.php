<?php
/**
 * Solwin Infotech
 * Solwin Previous Next Products Extension
 *
 * @category   Solwin
 * @package    Solwin_PrevNext
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\PrevNext\Model\Source;

class Display extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function getAllOptions() {
        $options = [
            ['label' => 'Only Text', 'value' => 1],
            ['label' => 'Only Image', 'value' => 2],
            ['label' => 'Image and Text Both', 'value' => 3],
        ];
        return $options;
    }

}