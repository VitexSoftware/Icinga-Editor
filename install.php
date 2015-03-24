<?php

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'IEImporter.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Icinga Editor - iniciace databáze')));
$oPage->addPageColumns();

if ($oPage->isPosted()) {

    $Params = array('generate' => true);
    $Public = $oPage->getRequestValue('public');
    if ($Public) {
        $Params['public'] = true;
    }
    $Importer = new IEImporter($Params);
    if ($oPage->getRequestValue('dbinit') == 'on') {
        $Importer->dbInit();
    }
    $Importer->importCfg($oPage->getRequestValue('maincfg'));
}

$oPage->addItem(new IEPageBottom());

$ImportForm = new EaseHtmlForm('ImportForm');
$ImportForm->addItem(new EaseLabeledTextInput('maincfg', constant('CFG_DIRECTORY') . 'icinga.cfg', _('hlavní soubor konfigurace')));

$ImportForm->addItem(new EaseLabeledCheckbox('dbinit', null, _('Znovu vytvořit strukturu databáze')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseJQuerySubmitButton('submit', _('importovat'), _('Spustí proces importu')));

$oPage->columnII->addItem(new EaseHtmlFieldSet(_('parametry inicializace'), $ImportForm));

$oPage->draw();
