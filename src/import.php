<?php

namespace Icinga\Editor;

/**
 * Import konfigurace ze souboru
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

if ($oPage->isPosted()) {

    $params = [];
    $public = $oPage->getRequestValue('public');
    if ($public) {
        $params['public'] = true;
    }
    $generate = $oPage->getRequestValue('generate');
    if ($public) {
        $params['generate'] = true;
    }
    $importer = new Engine\Importer($params);

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

$oPage->addItem(new UI\PageTop(_('Import konfigurace')));

$importForm = new \Ease\TWB\Form('CfgFileUp', null, 'POST', null,
    ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']);
$importForm->addInput(new \Ease\Html\TextareaTag('cfgtext', ''),
    _('konfigurační fragment'));
$importForm->addInput(new \Ease\Html\InputFileTag('cfgfile'),
    _('configuration file'));
$importForm->addInput(new UI\TWBSwitch('public'),
    _('Importovat data jako veřejná'));
$importForm->addInput(new UI\TWBSwitch('generate'),
    _('Generovat do konfigurace'));
$importForm->addItem(new \Ease\TWB\SubmitButton(_('importovat'), 'success',
    ['title' => _('zahájí import konfigurace')]));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Import konfigurace'),
    'warning', $importForm));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
