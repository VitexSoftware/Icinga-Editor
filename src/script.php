<?php
namespace Icinga\Editor;

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
            $scriptID = $script->saveToSQL();

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

$oPage->addItem(new UI\PageTop(_('Editace skriptu') . ' ' . $script->getName()));


switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new EaseContainer;
        $form->addItem(new \Ease\Html\H2Tag($script->getName()));

        $confirmator = $form->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')), 'danger');

        $confirmator->addItem(new \Ease\TWB\Well(nl2br($script->getDataValue('body'))));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?' . $script->myKeyColumn . '=' . $script->getID(), _('Ne') . ' ' . \Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&' . $script->myKeyColumn . '=' . $script->getID(), _('Ano') . ' ' . \Ease\TWB\Part::glyphIcon('remove'), 'danger'));


        break;
    default :
        $scriptEditor = new IECfgEditor($script);

        $form = new \Ease\TWB\Form('Script', 'script.php', 'POST', $scriptEditor, array('class' => 'form-horizontal'));

        if (!$script->getId()) {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        }
        break;
}
$oPage->addItem(new UI\PageBottom());


$infopanel = new IEInfoBox($script);
$tools = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
if ($script->getId()) {
    $tools->addItem($script->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning', $script->transferForm()));

    $command = new IECommand;

    $usages = $command->getColumnsFromMySQL(array($command->getMyKeyColumn(), $command->nameColumn), array('script_id' => $command->getId()), $command->nameColumn, $command->getMyKeyColumn());

    if (count($usages)) {
        $usedBy = new \Ease\TWB\Panel(_('Používající příkazy'));



        $listing = new \Ease\Html\UlTag(null, array('class' => 'list-group'));
        foreach ($usages as $usage) {

            if (!isset($usage[$command->nameColumn])) {
                $usage[$command->nameColumn] = 'n/a';
            }

            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('command.php?command_id=' . $usage['command_id'], $usage[$command->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }

        EaseContainer::addItemCustom($listing, $usedBy);

        $infopanel->addItem($usedBy);
    }
}

$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new \Ease\TWB\Panel(_('Skript') . ' <strong>' . $script->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
