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

class Sortorderselect
extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function getAllOptions() {
        $options = [
            ['label' => 'Default Category Sorting', 'value' => 0],
            ['label' => 'Set Your Custom Sorting', 'value' => 1],
        ];
        return $options;
    }

}