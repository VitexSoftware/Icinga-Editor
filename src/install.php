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
require_once 'IEImporter.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor - iniciace databáze')));
$oPage->addPageColumns();

if ($oPage->isPosted()) {

    $params = ['generate' => true];
    $public = $oPage->getRequestValue('public');
    if ($public) {
        $params['public'] = true;
    }
    $importer = new Engine\Importer($params);
    if ($oPage->getRequestValue('dbinit') == 'on') {
        $importer->dbInit();
    }
    $importer->importCfg($oPage->getRequestValue('maincfg'));
}

$oPage->addItem(new UI\PageBottom());

$importForm = new \Ease\Html\Form(['name' => 'ImportForm']);
$importForm->addItem(new EaseLabeledTextInput('maincfg',
                constant('CFG_DIRECTORY') . 'icinga.cfg', _('hlavní soubor konfigurace')));

$importForm->addItem(new EaseLabeledCheckbox('dbinit', null,
                _('Znovu vytvořit strukturu databáze')));
$importForm->addItem('<br clear = "all">');
$importForm->addItem(new \Ease\JQuery\SubmitButton('submit', _('importovat'),
                _('Spustí proces importu')));

$oPage->columnII->addItem(new \Ease\Html\FieldSet(_('parametry inicializace'),
                $importForm));

$oPage->draw();
