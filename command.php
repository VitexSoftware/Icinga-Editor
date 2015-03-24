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

switch ($oPage->getRequestValue('action')) {
    case 'export':
        $command->transfer($oPage->getRequestValue('destination'));
        break;
    default :
        if ($oPage->isPosted()) {
            $command->takeData($_POST);
            if (!$command->getName()) {
                $oUser->addStatusMessage(_('Není zadán název'), 'warning');
            }
            $commandID = $command->saveToMySQL();

            if (is_null($commandID)) {
                $oUser->addStatusMessage(_('Příkaz nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Příkaz byl uložen'), 'success');
            }
        }
}



$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $command->delete();
}

$service = new IEService;

$oPage->addItem(new IEPageTop(_('Editace příkazu') . ' ' . $command->getName()));
$oPage->addPageColumns();

if ($command->getId()) {
    $usages = $service->getColumnsFromMySQL(array($service->getMyKeyColumn(), $service->nameColumn), array('check_command' => $command->getName()), $service->nameColumn, $service->getMyKeyColumn());
    $oPage->columnI->addItem($command->ownerLinkButton());
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
}



switch ($oPage->getRequestValue('action')) {
    case 'delete':

        $oPage->columnII->addItem(new EaseHtmlH2Tag($command->getName()));

        $confirmator = $oPage->columnII->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new EaseTWBLinkButton('?' . $command->myKeyColumn . '=' . $command->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $command->myKeyColumn . '=' . $command->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));


        break;
    default :
        $commandEditor = new IECfgEditor($command);

        $form = $oPage->columnII->addItem(new EaseHtmlForm('Command', 'command.php', 'POST', $commandEditor, array('class' => 'form-horizontal')));

        if (!$command->getId()) {
            $form->addItem(new EaseTWSubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
        }
        $oPage->columnIII->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $command->transferForm()));
        break;
}


$oPage->addItem(new IEPageBottom());

$oPage->draw();
