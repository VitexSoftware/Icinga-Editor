<?php

/**
 * Data smluv ke kontrole
 *
 * @package    SystemDBFinance
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2014 Vitex@hippy.cz (C)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEDataSource.php';

$oPage->onlyForLogged();

$class = $oPage->getRequestValue('class');
if ($class) {
    if (file_exists('classes/' . $class . '.php')) {
        require_once 'classes/' . $class . '.php';
    }
    $commands = new IEDataSource(new $class);
    $commands->output();
}
