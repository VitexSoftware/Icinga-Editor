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

$oPage->addItem(new IEPageTop(_('Icinga Editor - znovuvytvoření struktury databáze')));

if ($oPage->getRequestValue('dbinit') ) {
    $Importer = new IEImporter;
    $Importer->dbInit();
    $oPage->column2->addItem(new EaseTWBLinkButton('wizard.php', _('vytvořit konfiguraci')));
    $oPage->column3->addItem(new EaseTWBLinkButton('import.php', _('importovat konfiguraci')));
} else {
    $ImportForm = new EaseHtmlForm('ImportForm');
    $oUser->addStatusMessage(_('Tato akce nevratně smaže veškerou konfiguraci. Opravdu to chcete udělat ?'));
    $ImportForm->addItem(new EaseLabeledCheckbox('dbinit', null, _('Vím co dělám')));
    $ImportForm->addItem(new EaseJQuerySubmitButton('submit', _('Budiž!')));

    $oPage->column2->addItem(new EaseHtmlFieldSet(_('Znovu vytvořit strukturu databáze'), $ImportForm));
}


$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
