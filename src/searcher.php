<?php

namespace Icinga\Editor;

/**
 * prohlížeč databáze
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once './classes/IESearcher.php';

$query = $oPage->getRequestValue('q', 'string');

$found = [];

$searcher = new Searcher;

header('ContentType: text/json');

if (strlen($query) > 1) {
    $results = $searcher->searchAll(\Ease\Shared::db()->EaseAddslashes($query));

    foreach ($results as $rectype => $records) {
        foreach ($records as $recid => $record) {
            $found[] = ['url' => $rectype . '.php?' . $rectype . '_id=' . $recid, 'name' => current($record),
                'type' => $rectype, 'what' => $record['what']];
        }
    }
}
echo json_encode($found);


