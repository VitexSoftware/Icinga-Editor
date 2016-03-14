<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor - znovuvytvoření struktury databáze')));
$oPage->addPageColumns();


if ($oPage->getRequestValue('dbinit')) {
    $importer = new IEImporter;
    $importer->dbInit();
    $oPage->columnII->addItem(new \Ease\TWB\LinkButton('wizard.php', _('vytvořit konfiguraci')));
    $oPage->columnIII->addItem(new \Ease\TWB\LinkButton('import.php', _('importovat konfiguraci')));
} else {
    $importForm = new \Ease\Html\Form('ImportForm');
    $oUser->addStatusMessage(_('Tato akce nevratně smaže veškerou konfiguraci. Opravdu to chcete udělat ?'));
    $importForm->addItem(new EaseLabeledCheckbox('dbinit', null, _('Vím co dělám')));
    $importForm->addItem(new \Ease\JQuery\SubmitButton('submit', _('Budiž!')));

    $oPage->columnII->addItem(new \Ease\Html\FieldSet(_('Znovu vytvořit strukturu databáze'), $importForm));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
