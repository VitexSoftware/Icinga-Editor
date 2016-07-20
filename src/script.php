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

$script = new Script($oPage->getRequestValue('script_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'export':
        $script->transfer($oPage->getRequestValue('destination'));
        break;
    default :
        if ($oPage->isPosted()) {
            $script->takeData($_POST);
            if (isset($_FILES['upload'])) {
                if (!$script->getDataValue('filename')) {
                    $script->setDataValue('filename',
                        basename($_FILES['upload']['name']));
                }
                $script->setDataValue('body',
                    file_get_contents($_FILES['upload']['tmp_name']));
                $script->addStatusMessage(_('Skript byl nahran na server'),
                    'success');
            }


            if (!$script->getName()) {
                $oUser->addStatusMessage(_('Není zadán název'), 'warning');
            }
            $scriptID = $script->saveToSQL();

            if (is_null($scriptID)) {
                $oUser->addStatusMessage(_('Skript nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Skript byl uložen'), 'success');
            }
        }
}


$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $script->delete();
}

$oPage->addItem(new UI\PageTop(_('Editace skriptu').' '.$script->getName()));


switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new \Ease\Container;
        $form->addItem(new \Ease\Html\H2Tag($script->getName()));

        $confirmator = $form->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')),
            'danger');

        $confirmator->addItem(new \Ease\TWB\Well(nl2br($script->getDataValue('body'))));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?'.$script->myKeyColumn.'='.$script->getID(),
            _('Ne').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$script->myKeyColumn.'='.$script->getID(),
            _('Ano').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));


        break;
    default :
        $scriptEditor = new UI\CfgEditor($script);

        $form = new \Ease\TWB\Form('Script', 'script.php', 'POST',
            $scriptEditor,
            ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data']);

        $form->addInput(new \Ease\Html\InputFileTag('upload'),
            _('Odeslat soubor'), 'script.sh', _('(Textarea bude prepsana)'));

        if (!$script->getId()) {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        }
        break;
}
$oPage->addItem(new UI\PageBottom());


$infopanel = new UI\InfoBox($script);
$tools     = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
if ($script->getId()) {
    $tools->addItem($script->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
        $script->transferForm()));

    $command = new Engine\Command;

    $usages = $command->getColumnsFromSQL([$command->getMyKeyColumn(), $command->nameColumn],
        ['script_id' => $command->getId()], $command->nameColumn,
        $command->getMyKeyColumn());

    if (count($usages)) {
        $usedBy = new \Ease\TWB\Panel(_('Používající příkazy'));



        $listing = new \Ease\Html\UlTag(null, ['class' => 'list-group']);
        foreach ($usages as $usage) {

            if (!isset($usage[$command->nameColumn])) {
                $usage[$command->nameColumn] = 'n/a';
            }

            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('command.php?command_id='.$usage['command_id'],
                $usage[$command->nameColumn])
                , ['class' => 'list-group-item'])
            );
        }

        \Ease\Container::addItemCustom($listing, $usedBy);

        $infopanel->addItem($usedBy);
    }
}

$pageRow = new \Ease\TWB\Row();
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6,
    new \Ease\TWB\Panel(_('Skript').' <strong>'.$script->getName().'</strong>',
    'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
