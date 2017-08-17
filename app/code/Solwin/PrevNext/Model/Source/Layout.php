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

class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function getAllOptions() {
        $options = [
            ['label' => 'Layout 1', 'value' => 1],
            ['label' => 'Layout 2', 'value' => 2],
            ['label' => 'Layout 3', 'value' => 3],
        ];
        return $options;
    }

}