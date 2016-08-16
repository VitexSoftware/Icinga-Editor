<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostId = $oPage->getRequestValue('host_id', 'int');
$host   = new Engine\Host($hostId);

if ($oPage->getRequestValue('format') == 'ps1') {
    $generator = new NSCPConfigPS1Generator($host);
} else {
    $generator = new NSCPConfigBatGenerator($host);
}

$generator->getCfg();
