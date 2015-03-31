<?php

/**
 * Import ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

if ($oPage->isPosted()) {
    $importer = new IECommand();
    $success = 0;
    $cfgText = $oPage->getRequestValue('cfgtext');
    if ($cfgText) {
        $success += $importer->importText($cfgText, array('command_type' => $oPage->getRequestValue('type')));
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $success += $importer->importFile($_FILES['cfgfile']['tmp_name'], array('command_type' => $oPage->getRequestValue('type')));
    }
    if ($success) {
        $oPage->addStatusMessage(sprintf(_('Příkaz %s byl naimportován'), $importer->getName()), 'success');
    } else {
        $oPage->addStatusMessage(_('Příkaz nebyl naimportován'), 'warning');
    }
} else {
    $oPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}


$oPage->addItem(new IEPageTop(_('Načtení příkazů ze souboru')));

$fileForm = new EaseTWBForm('CfgFileUp', null, 'POST', null, array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data'));
$fileForm->addInput(new EaseHtmlTextareaTag('cfgtext', ''), _('konfigurační fragment'));
$fileForm->addInput(new EaseHtmlInputFileTag('cfgfile', null), _('konfigurační soubor'));

$typeSelector = new EaseHtmlSelect('type', 'check');
$typeSelector->addItems(array('check' => 'check', 'notify' => 'notify', 'handler' => 'handler'));

$fileForm->addInput($typeSelector, _('druh vkládaných příkazů'));

$fileForm->addItem(new EaseTWSubmitButton(_('importovat'), 'success'));

$oPage->container->addItem(new EaseTWBPanel(_('Import příkazu do konfigurace'), 'success', $fileForm));

$oPage->addItem(new IEPageBottom());


$oPage->draw();

