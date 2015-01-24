<?php

/**
 * prohlížeč databáze
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once './classes/IEImporter.php';

$query = $oPage->getRequestValue('q', 'string');

$found = array();

$searcher = new IEImporter();

header('ContentType: text/json');

if (strlen($query) > 1) {
    $results = $searcher->searchAll(EaseShared::db()->EaseAddslashes($query));

    foreach ($results as $rectype => $records) {
        foreach ($records as $recid => $record) {
            $found[] = array('url' => $rectype . '.php?' . $rectype . '_id=' . $recid, 'name' => current($record), 'type' => $rectype, 'what' => $record['what']);
        }
    }
}
echo json_encode($found);


