<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - potvrzení nasazení konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$host_id = $oPage->getRequestValue('host_id', 'int');
$hash = $oPage->getRequestValue('hash');

if ($host_id && $hash) {
    $host = new IEHost($host_id);
    $host->setDataValue('config_hash', $hash);
    if ($host->saveToSQL()) {
        echo sprintf(_('Konfigurace %s potvrzena'), $host->getName());
    } else {
        echo sprintf(_('Chyba potvrzení konfigurace'), $host->getName());
    }
    echo "\n<br>" . _('Sledovat cestu k hostu') . ":\n";

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
