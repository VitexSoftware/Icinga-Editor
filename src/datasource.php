<?php

namespace Icinga\Editor;

/**
 * DataGrid data source
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014-2017 Vitex@hippy.cz (C)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$class = $oPage->getRequestValue('class');
if ($class) {
    $commands = new DataSource(new $class);
    $commands->output();
}
