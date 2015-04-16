<?php

/**
 * Import konfigurace ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEImporter.php';

$oPage->onlyForLogged();

if ($oPage->isPosted()) {

    $params = array();
    $public = $oPage->getRequestValue('public');
    if ($public) {
        $params['public'] = true;
    }
    $generate = $oPage->getRequestValue('generate');
    if ($public) {
        $params['generate'] = true;
    }
    $importer = new IEImporter($params);

    $cfgText = $oPage->getRequestValue('cfgtext');
    if ($cfgText) {
        $importer->importCfgText($cfgText, $params);
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $importer->importCfgFile($_FILES['cfgfile']['tmp_name']);
    }
} else {
    $oPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}

$oPage->addItem(new IEPageTop(_('Import konfigurace')));

$importForm = new EaseTWBForm('CfgFileUp', null, 'POST', null, array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data'));
$importForm->addInput(new EaseHtmlTextareaTag('cfgtext', ''), _('konfigurační fragment'));
$importForm->addInput(new EaseHtmlInputFileTag('cfgfile'), _('konfigurační soubor'));
$importForm->addInput(new IETWBSwitch('public'), _('Importovat data jako veřejná'));
$importForm->addInput(new IETWBSwitch('generate'), _('Generovat do konfigurace'));
$importForm->addItem(new EaseTWSubmitButton(_('importovat'), 'success', array('title' => _('zahájí import konfigurace'))));

$oPage->container->addItem(new EaseTWBPanel(_('Import konfigurace'), 'warning', $importForm));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
