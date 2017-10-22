<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - nastavení uživatele
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$prefs = new Preferences();


if ($oPage->isPosted()) {
    //unset($_REQUEST['']);
    if ($prefs->savePrefs($_REQUEST)) {
        $oPage->addStatusMessage(_('Preferneces was saved'), 'success');
    } else {
        $oPage->addStatusMessage(_('Preferences was not saved'), 'warning');
    }
}

$oPage->addItem(new UI\PageTop(_('Preferences')));
$oPage->addPageColumns();

$form = new UI\PreferencesForm('prefs');
$form->fillUp($prefs->getPrefs());

$oPage->columnII->addItem($form);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
