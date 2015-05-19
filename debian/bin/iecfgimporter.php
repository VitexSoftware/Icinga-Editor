#!/usr/bin/env php
<?php
/**
 * Import konfigurace ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
chdir('/usr/share/icinga-editor');

require_once 'includes/IEInit.php';
require_once 'classes/IEImporter.php';

$params = array('public' => true, 'generate' => true);

$importer = new IEImporter($params);
$importer->importCfgFile('/etc/icinga/icinga.cfg');

foreach ($importer->files as $cfgFile) {
    if ($cfgFile == '/etc/icinga/icinga.cfg') {
        continue;
    }
    if (unlink($cfgFile)) {
        echo $cfgFile . " X\n";
    }
}


foreach ($oPage->getStatusMessages() as $type => $message) {
    echo "$type: $message \n";
}