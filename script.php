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

$oPage->onlyForLogged();

$script = new IEScript($oPage->getRequestValue('script_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'export':
        $script->transfer($oPage->getRequestValue('destination'));
        break;
    default :
        if ($oPage->isPosted()) {
            $script->takeData($_POST);
            if (!$script->getName()) {
                $oUser->addStatusMessage(_('Není zadán název'), 'warning');
            }
            $scriptID = $script->saveToMySQL();

            if (is_null($scriptID)) {
                $oUser->addStatusMessage(_('Příkaz nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Příkaz byl uložen'), 'success');
            }
        }
}



$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $script->delete();
}

$oPage->addItem(new IEPageTop(_('Editace skriptu') . ' ' . $script->getName()));


switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new EaseContainer;
        $form->addItem(new EaseHtmlH2Tag($script->getName()));

        $confirmator = $form->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');

        $confirmator->addItem(new EaseTWBWell(nl2br($script->getDataValue('body'))));
        $confirmator->addItem(new EaseTWBLinkButton('?' . $script->myKeyColumn . '=' . $script->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $script->myKeyColumn . '=' . $script->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));


        break;
    default :
        $scriptEditor = new IECfgEditor($script);

        $form = new EaseTWBForm('Script', 'script.php', 'POST', $scriptEditor, array('class' => 'form-horizontal'));

        if (!$script->getId()) {
            $form->addItem(new EaseTWSubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
        }
        break;
}
$oPage->addItem(new IEPageBottom());


$infopanel = new IEInfoBox($script);
$tools = new EaseTWBPanel(_('Nástroje'), 'warning');
if ($script->getId()) {
    $tools->addItem($script->deleteButton());
    $tools->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $script->transferForm()));

    $command = new IECommand;

    $usages = $command->getColumnsFromMySQL(array($command->getMyKeyColumn(), $command->nameColumn), array('script_id' => $command->getId()), $command->nameColumn, $command->getMyKeyColumn());

    if (count($usages)) {
        $usedBy = new EaseTWBPanel(_('Používající příkazy'));



        $listing = new EaseHtmlUlTag(null, array('class' => 'list-group'));
        foreach ($usages as $usage) {

            if (!isset($usage[$command->nameColumn])) {
                $usage[$command->nameColumn] = 'n/a';
            }

            $listing->addItem(
                new EaseHtmlLiTag(
                new EaseHtmlATag('command.php?command_id=' . $usage['command_id'], $usage[$command->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }

        EaseContainer::addItemCustom($listing, $usedBy);

        $infopanel->addItem($usedBy);
    }
}

$pageRow = new EaseTWBRow;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new EaseTWBPanel(_('Skript') . ' <strong>' . $script->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
