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
require_once '../includes/IEInit.php';

if (!isset($argv[1])) {
    die("which file to import ?");
} else {
    if (file_exists($argv[1])) {
        $cfgFile = $argv[1];
    } else {
        die("file $cfgFile does not exists");
    }
}

$params = ['public' => true, 'generate' => true];

$importer = new Icinga\Editor\Engine\Importer($params);
$importer->importCfgFile($cfgFile);

foreach ($importer->files as $cfgFile) {
    if ($cfgFile == '/etc/icinga/icinga.cfg') {
        continue;
    }
    if (rename($cfgFile, $cfgFile . '.disabled')) {
        echo $cfgFile . " imported\n";
    }
}


foreach (\Ease\Shared::webPage()->getStatusMessages() as $type => $messages) {
    echo "====== $type ====== \n";
    foreach ($messages as $message) {
        echo $message . "\n";
    }
}
