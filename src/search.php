<?php

namespace Icinga\Editor;

/**
 * Search page
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$query = $oPage->getRequestValue('search', 'string');

$found = [];

$searcher = new Searcher($oPage->getRequestValue('table', 'string'),
        $oPage->getRequestValue('column', 'string'));

if (strlen($query) < 2) {
    $oPage->addStatusMessage(_('Search term is too short'), 'warning');
} else {

    $results = $searcher->searchAll(\Ease\Shared::db()->EaseAddslashes($query));

    foreach ($results as $rectype => $records) {
        foreach ($records as $recid => $record) {
            $found[] = ['url' => $rectype . '.php?' . $rectype . '_id=' . $recid, 'name' => current($record),
                'type' => $rectype,
                'what' => $record['what']];
        }
    }

    if (count($found) == 1) {
        $oPage->addStatusMessage(_('Only one result found', 'success'));
        header('Location: ' . $found[0]['url'] . '&search=' . $query);
        exit;
    }
}
$oPage->addItem(new UI\PageTop(_('Search results')));

$listing = new \Ease\Html\UlTag(null, ['class' => 'list-group']);

foreach ($found as $foundItem) {
    $listing->addItem(
            new \Ease\Html\LiTag(
                    new \Ease\Html\ATag(
                            $foundItem['url'],
                            $foundItem['type'] . '&nbsp;<h4>' . $foundItem['name'] . '</h4>&nbsp;' . str_replace($query,
                                    '<strong>' . $query . '</strong>', $foundItem['what'])
                    )
                    , ['class' => 'list-group-item'])
    );
}

$oPage->addItem(new \Ease\TWB\Container($listing));


$oPage->addItem(new UI\PageBottom());

$oPage->draw();

