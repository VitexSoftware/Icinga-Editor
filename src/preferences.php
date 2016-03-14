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
require_once 'classes/IEPreferencesForm.php';
require_once 'classes/IEPreferences.php';

$oPage->onlyForLogged();

$prefs = new IEPreferences;

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

$form = new IEPreferencesForm('prefs');
$form->fillUp($prefs->getPrefs());

$oPage->columnII->addItem($form);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
