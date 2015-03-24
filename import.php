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
require_once 'classes/IEImporter.php';

$oPage->onlyForLogged();

if ($oPage->isPosted()) {

    $Params = array();
    $Public = $oPage->getRequestValue('public');
    if ($Public) {
        $Params['public'] = true;
    }
    $Generate = $oPage->getRequestValue('generate');
    if ($Public) {
        $Params['generate'] = true;
    }
    $Importer = new IEImporter($Params);

    $CfgText = $oPage->getRequestValue('cfgtext');
    if ($CfgText) {
        $Importer->importCfgText($CfgText);
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $Importer->importCfgFile($_FILES['cfgfile']['tmp_name']);
    }
} else {
    $oPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}

$oPage->addItem(new IEPageTop(_('Import konfigurace')));
$oPage->addPageColumns();

$ImportForm = new EaseHtmlForm('CfgFileUp', null, 'POST', null, array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data'));
$ImportForm->addItem(new EaseLabeledTextarea('cfgtext', '', _('konfigurační fragment')));
$ImportForm->addItem(new EaseLabeledFileInput('cfgfile', null, _('konfigurační soubor')));

$ImportForm->addItem(new EaseLabeledCheckbox('public', null, _('Importovat data jako veřejná')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseLabeledCheckbox('generate', null, _('Generovat do konfigurace')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseJQuerySubmitButton('Submit', _('importovat'), _('zahájí import konfigurace')));

$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnII->addItem(new EaseHtmlFieldSet(_('Import konfigurace'), $ImportForm));

$oPage->columnI->addItem('<div class="well">' . _('Vložte konfigurační fragment nagiosu / icingy') . '</div>');

$oPage->addItem(new IEPageBottom());

$oPage->draw();
