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
require_once 'IEImporter.php';


$OPage->onlyForLogged();

if ($OPage->isPosted()) {

    $Params = array();
    $Public = $OPage->getRequestValue('public');
    if ($Public) {
        $Params['public'] = true;
    }
    $Generate = $OPage->getRequestValue('generate');
    if ($Public) {
        $Params['generate'] = true;
    }
    $Importer = new IEImporter($Params);


    $CfgText = $OPage->getRequestValue('cfgtext');
    if ($CfgText) {
        $Importer->importCfgText($CfgText);
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $Importer->importCfgFile($_FILES['cfgfile']['tmp_name']);
    }
} else {
    $OPage->addStatusMessage(_('Zadejte konfigurační fragment příkazu, nebo zvolte soubor k importu'));
}


$OPage->addItem(new IEPageTop(_('Import konfigurace')));

$ImportForm = new EaseHtmlForm('CfgFileUp', null, 'POST', null, array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data'));
$ImportForm->addItem(new EaseLabeledTextarea('cfgtext', '', _('konfigurační fragment')));
$ImportForm->addItem(new EaseLabeledFileInput('cfgfile', null, _('konfigurační soubor')));

$ImportForm->addItem(new EaseLabeledCheckbox('public', null, _('Importovat data jako veřejná')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseLabeledCheckbox('generate', null, _('Generovat do konfigurace')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseJQuerySubmitButton('Submit', _('importovat'), _('zahájí import konfigurace')));

$OPage->AddCss('
input.ui-button { width: 100%; }
');

$OPage->column2->addItem(new EaseHtmlFieldSet(_('Import konfigurace'), $ImportForm));

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
