<?php

namespace Icinga\Editor;

/**
 * Import ze souboru
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

if ($oPage->isPosted()) {
    $importer = new Engine\Command();
    $success  = 0;
    $cfgText  = $oPage->getRequestValue('cfgtext');
    if ($cfgText) {
        $success += $importer->importText($cfgText,
            ['command_type' => $oPage->getRequestValue('type')]);
    }

    if (isset($_FILES['cfgfile']['tmp_name']) && strlen(trim($_FILES['cfgfile']['tmp_name']))) {
        $success += $importer->importFile($_FILES['cfgfile']['tmp_name'],
            ['command_type' => $oPage->getRequestValue('type')]);
    }
    if ($success) {
        $oPage->addStatusMessage(sprintf(_('Command %s was imported'),
                $importer->getName()), 'success');
    } else {
        $oPage->addStatusMessage(_('Command was not imported'), 'warning');
    }
} else {
    $oPage->addStatusMessage(_('Enter configuration fragment or choose config file'));
}


$oPage->addItem(new UI\PageTop(_('Read commands from file')));

$fileForm = new \Ease\TWB\Form('CfgFileUp', null, 'POST', null,
    ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']);
$fileForm->addInput(new \Ease\Html\TextareaTag('cfgtext', ''),
    _('konfigurační fragment'));
$fileForm->addInput(new \Ease\Html\InputFileTag('cfgfile', null),
    _('configuration file'));

$typeSelector = new \Ease\Html\Select('type', 'check');
$typeSelector->addItems(['check' => 'check', 'notify' => 'notify', 'handler' => 'handler']);

$fileForm->addInput($typeSelector, _('Included commands type'));

$fileForm->addItem(new \Ease\TWB\SubmitButton(_('importovat'), 'success'));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Import command to configuration'),
        'success', $fileForm));

$oPage->addItem(new UI\PageBottom());


$oPage->draw();

