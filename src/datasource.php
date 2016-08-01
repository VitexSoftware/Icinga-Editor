<?php

namespace Icinga\Editor;

/**
 * Posílá vyžádaná data do gridu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014 Vitex@hippy.cz (C)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$class = $oPage->getRequestValue('class');
if ($class) {
    if (file_exists('classes/'.$class.'.php')) {
        require_once 'classes/'.$class.'.php';
    }
    $commands = new DataSource(new $class);
    $commands->output();
}
