<?php

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
    $host = new IEHost();
    $host->setMyKey($host_id);
    $host->setDataValue('config_hash', $hash);
    if ($host->updateToMySQL()) {
        echo _('Konfigurace potvrzena');
    } else {
        echo _('Chyba potvrzení konfigurace');
    }
} else {
    die(_('Chybné volání'));
}
