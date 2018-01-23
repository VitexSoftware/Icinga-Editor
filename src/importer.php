<?php

namespace Icinga\Editor;

/**
 * Configuration importer
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$class = $oPage->getRequestValue('class');
if ($oPage->isPosted() && $class) {
    $data     = $_POST;
    $importer = new Engine\Importer();
    $recorder = $importer->parseClasses[$class];
    unset($data[$recorder->keyColumn]);
    unset($data[$recorder->userColumn]);
    $recorder->importDataRow($data);
    $recorder->setMyKeyColumn($recorder->nameColumn);
    $iresult  = $recorder->saveToSQL(null, true);
    $recorder->restoreObjectIdentity();
    if ($iresult) {
        $recorder->loadFromSql($recorder->getName());
        echo $importer->parseClasses[$class]->keyword.': '.$iresult.' (#'.$recorder->getId().')';
    } else {
        echo 'false';
    }
} else {
    echo 'GET?';
}



