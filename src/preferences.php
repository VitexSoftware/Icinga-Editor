<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - nastavení uživatele
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$prefs = new IEPreferences();


if ($oPage->isPosted()) {
    //unset($_REQUEST['']);
    if ($prefs->savePrefs($_REQUEST)) {
        $oPage->addStatusMessage(_('Předvolby byly uloženy'), 'success');
    } else {
        $oPage->addStatusMessage(_('Předvolby nebyly uloženy'), 'warning');
    }
}

$oPage->addItem(new UI\PageTop(_('Předvolby')));
$oPage->addPageColumns();

$form = new UI\PreferencesForm('prefs');
$form->fillUp($prefs->getPrefs());

$oPage->columnII->addItem($form);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
