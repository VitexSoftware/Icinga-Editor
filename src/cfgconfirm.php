<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - potvrzení nasazení konfigurace
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$host_id = $oPage->getRequestValue('host_id', 'int');
$hash = $oPage->getRequestValue('hash');

if ($host_id && $hash) {
    $host = new Engine\Host($host_id);
    $oUser = new \Icinga\Editor\User($host->getOwnerID());
    $host->setDataValue('config_hash', $hash);
    if ($host->saveToSQL()) {
        echo sprintf(_('Configuration Confirmed'), $host->getName());
        $extCmd = new ExternalCommand();
        $extCmd->addCommand('ADD_HOST_COMMENT;' . $host->getName() . ';1;' . $oUser->getUserLogin() . ';' . _('Sensor Configuration Confirmed'));
        $extCmd->executeAll();
    } else {
        echo sprintf(_('Configuration Confirmation Error'), $host->getName());
    }
    echo "\n<br>" . _('Watch route to host') . ":\n";

    if (isset($_SERVER['REQUEST_SCHEME'])) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    } else {
        $scheme = 'http';
    }

    $enterPoint = $scheme . '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . '/';
    $enterPoint = str_replace('\\', '', $enterPoint); //Win Hack
    $confirmUrl = $enterPoint . 'watchroute.php?action=parent&host_id=' . $host_id . '&ip=' . $_SERVER['REMOTE_ADDR'];

    echo '<a href="' . $confirmUrl . '"> ' . $confirmUrl . ' </a>';
} else {
    die(_('Chybné volání'));
}
