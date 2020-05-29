<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Obtain configuration script for NSClient ++ deploy
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$hostId = $oPage->getRequestValue('host_id', 'int');
$host = new Engine\Host($hostId);

$forceUsername = $oPage->getRequestValue('user');
if (!is_null($forceUsername)) {
    \Ease\Shared::user(new \Icinga\Editor\User($forceUsername));
} else {
    \Ease\Shared::user(new \Icinga\Editor\User($host->getOwnerID()));
}

if ($oPage->getRequestValue('format') == 'ps1') {
    $generator = new NSCPConfigPS1Generator($host);
} else {
    $generator = new NSCPConfigBatGenerator($host);
}

$generator->getCfg();
