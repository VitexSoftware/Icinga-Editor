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

$query = $oPage->getRequestValue('search', 'string');

$found = array();

$searcher = new IEImporter();



$results = $searcher->searchAll(EaseShared::db()->EaseAddslashes($query));

foreach ($results as $rectype => $records) {
    foreach ($records as $recid => $record) {
        $found[] = array('url' => $rectype . '.php?' . $rectype . '_id=' . $recid, 'name' => $record, 'type' => $rectype);
    }
}

if (count($found) == 1) {
    header('Location: ' . $found[0]['url']);
    exit;
}

$oPage->addItem(new IEPageTop(_('Výsledky hledání')));

$listing = new EaseHtmlUlTag(null, array('class' => 'list-group'));

foreach ($found as $foundItem) {
    $listing->addItem(new EaseHtmlLiTag(new EaseHtmlATag($foundItem['url'], $foundItem['name'] . ' (' . $foundItem['type'] . ')'), array('class' => 'list-group-item')));
}

$oPage->addItem(new EaseTWBContainer($listing));

$oPage->addItem(new IEPageBottom());

$oPage->draw();

