#!/usr/bin/env php
<?php
//namespace Icinga\Editor;

/**
 * Import konfigurace ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
//require_once '/usr/share/icinga-editor/includes/IEInit.php';
require_once '../includes/IEInit.php';

$params = array('public' => true, 'generate' => true);

$importer = new Icinga\Editor\Engine\Importer($params);
$importer->importCfgFile('/etc/icinga/icinga.cfg');

foreach ($importer->files as $cfgFile) {
    if ($cfgFile == '/etc/icinga/icinga.cfg') {
        continue;
    }
    if (rename($cfgFile, $cfgFile.'.disabled')) {
        echo $cfgFile." imported\n";
    }
}


foreach ($oPage->getStatusMessages() as $type => $message) {
    echo "$type: $message \n";
}