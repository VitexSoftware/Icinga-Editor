<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - zasílá skript pro deploy
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

//$oPage->onlyForLogged();

$script_id = $oPage->getRequestValue('script_id', 'int');
if ($script_id) {
    $script = new IEScript($script_id);
    $script->getCfg();
} else {
    die('script_id ?');
}
