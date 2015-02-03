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
require_once 'classes/IESearcher.php';

$query = $oPage->getRequestValue('search', 'string');

$found = array();

$searcher = new IESearcher($oPage->getRequestValue('table', 'string'), $oPage->getRequestValue('column', 'string'));

if (strlen($query) < 2) {
    $oPage->addStatusMessage(_('Vyheldávaný řetězec je příliš krátký'), 'warning');
} else {

    $results = $searcher->searchAll(EaseShared::db()->EaseAddslashes($query));

    foreach ($results as $rectype => $records) {
        foreach ($records as $recid => $record) {
            $found[] = array('url' => $rectype . '.php?' . $rectype . '_id=' . $recid, 'name' => current($record), 'type' => $rectype,
              'what' => $record['what']);
        }
    }

    if (count($found) == 1) {
        $oPage->addStatusMessage(_('Nalezen pouze jeden výsledek', 'success'));
        header('Location: ' . $found[0]['url'] . '&search=' . $query);
        exit;
    }
}
$oPage->addItem(new IEPageTop(_('Výsledky hledání')));

$listing = new EaseHtmlUlTag(null, array('class' => 'list-group'));

foreach ($found as $foundItem) {
    $listing->addItem(
        new EaseHtmlLiTag(
        new EaseHtmlATag(
        $foundItem['url'], $foundItem['type'] . '&nbsp;<h4>' . $foundItem['name'] . '</h4>&nbsp;' . str_replace($query, '<strong>' . $query . '</strong>', $foundItem['what'])
        )
        , array('class' => 'list-group-item'))
    );
}

$oPage->addItem(new EaseTWBContainer($listing));


$oPage->addItem(new IEPageBottom());

$oPage->draw();

