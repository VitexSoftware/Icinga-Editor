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

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Icinga Editor - iniciace databáze')));

if ($OPage->isPosted()) {



    $Params = array('generate' => true);
    $Public = $OPage->getRequestValue('public');
    if ($Public) {
        $Params['public'] = true;
    }
    $Importer = new IEImporter($Params);
    if ($OPage->getRequestValue('dbinit') == 'on') {
        $Importer->dbInit();
    }
    $Importer->importCfg($OPage->getRequestValue('maincfg'));
}


$OPage->addItem(new IEPageBottom());

$ImportForm = new EaseHtmlForm('ImportForm');
$ImportForm->addItem(new EaseLabeledTextInput('maincfg', constant('CFG_DIRECTORY').'icinga.cfg', _('hlavní soubor konfigurace')));

$ImportForm->addItem(new EaseLabeledCheckbox('dbinit', null, _('Znovu vytvořit strukturu databáze')));
$ImportForm->addItem('<br clear="all">');
$ImportForm->addItem(new EaseJQuerySubmitButton('submit', _('importovat'), _('Spustí proces importu')));

$OPage->column2->addItem(new EaseHtmlFieldSet(_('parametry inicializace'), $ImportForm));

$OPage->draw();
?>
