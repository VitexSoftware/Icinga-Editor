<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IECommand.php';
require_once 'classes/IEService.php';
require_once 'classes/IECfgEditor.php';

$oPage->onlyForLogged();

$command = new IECommand($oPage->getRequestValue('command_id', 'int'));

if ($oPage->isPosted()) {
    $command->takeData($_POST);
    $CommandID = $command->saveToMySQL();
    if (is_null($CommandID)) {
        $oUser->addStatusMessage(_('Příkaz nebyl uložen'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Příkaz byl uložen'), 'success');
    }
}

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $command->delete();
}

$service = new IEService;

$usages = $service->getColumnsFromMySQL(array($service->getMyKeyColumn(), $service->nameColumn), array('check_command' => $command->getName()), $service->nameColumn, $service->getMyKeyColumn());




$oPage->addItem(new IEPageTop(_('Editace příkazu') . ' ' . $command->getName()));

$CommandEdit = new IECfgEditor($command);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Command', 'command.php', 'POST', $CommandEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($command->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($command->getmyKeyColumn(), $command->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');


if ($command->getId()) {
    $oPage->columnI->addItem($command->ownerLinkButton());
}

if (count($usages)) {
    $usedBy = new EaseTWBPanel(_('Používající služby'));
    $listing = $usedBy->addItem(new EaseHtmlUlTag(null, array('class' => 'list-group')));
    foreach ($usages as $usage) {
        $listing->addItem(
            new EaseHtmlLiTag(
            new EaseHtmlATag('service.php?service_id=' . $usage['service_id'], $usage[$service->nameColumn])
            , array('class' => 'list-group-item'))
        );
    }
    $form = $oPage->columnI->addItem($usedBy);
} else {
    $oPage->columnIII->addItem($command->deleteButton());
}


$oPage->addItem(new IEPageBottom());

$oPage->draw();
