<?php
namespace Icinga\Editor;

/**
 * Import ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$class = $oPage->getRequestValue('class');
if ($oPage->isPosted() && $class) {
    $data = $_POST;
    $importer = new Engine\Importer();
    $recorder = $importer->Classes[$class];
    unset($data[$recorder->myKeyColumn]);
    unset($data[$recorder->userColumn]);
    $recorder->importDataRow($data);
    $recorder->setMyKeyColumn($recorder->nameColumn);
    $iresult = $recorder->saveToSQL(null, true);
    $recorder->restoreObjectIdentity();
    if ($iresult) {
        $recorder->loadFromSql($recorder->getName());
        echo $importer->Classes[$class]->keyword . ': ' . $iresult . ' (#' . $recorder->getId() . ')';
    } else {
        echo 'false';
    }
} else {
    echo 'GET?';
}



