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

$oPage->addItem(new UI\PageTop(_('Editace příkazu') . ' ' . $command->getName()));







switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new EaseContainer;
        $form->addItem(new \Ease\Html\H2Tag($command->getName()));

        $confirmator = $form->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('?' . $command->myKeyColumn . '=' . $command->getID(), _('Ne') . ' ' . \Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&' . $command->myKeyColumn . '=' . $command->getID(), _('Ano') . ' ' . \Ease\TWB\Part::glyphIcon('remove'), 'danger'));


        break;
    default :
        $commandEditor = new IECfgEditor($command);

        $form = new \Ease\TWB\Form('Command', 'command.php', 'POST', $commandEditor, array('class' => 'form-horizontal'));

        if (!$command->getId()) {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        }
        break;
}
$oPage->addItem(new UI\PageBottom());


$infopanel = new IEInfoBox($command);
$tools = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
if ($command->getId()) {
    $tools->addItem($command->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning', $command->transferForm()));

    $service = new IEService;
    $usages = $service->getColumnsFromMySQL(array($service->getMyKeyColumn(), $service->nameColumn), array('check_command' => $command->getName()), $service->nameColumn, $service->getMyKeyColumn());
    if (count($usages)) {
        $usedBy = new \Ease\TWB\Panel(_('Používající služby'));
        $listing = $usedBy->addItem(new \Ease\Html\UlTag(null, array('class' => 'list-group')));
        foreach ($usages as $usage) {
            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('service.php?service_id=' . $usage['service_id'], $usage[$service->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }
        $infopanel->addItem($usedBy);
    }

    $contact = new IEContact;
    $hostNotify = $contact->getColumnsFromMySQL(array($contact->getMyKeyColumn(), $contact->nameColumn), array('host_notification_commands' => '%' . $command->getName() . '%'), $contact->nameColumn, $contact->getMyKeyColumn());
    $serviceNotify = $contact->getColumnsFromMySQL(array($contact->getMyKeyColumn(), $contact->nameColumn), array('service_notification_commands' => '%' . $command->getName() . '%'), $contact->nameColumn, $contact->getMyKeyColumn());
    $usages = array_merge($hostNotify, $serviceNotify);
    if (count($usages)) {
        $usedBy = new \Ease\TWB\Panel(_('Používající kontakty'));
        $listing = new \Ease\Html\UlTag(null, array('class' => 'list-group'));
        foreach ($usages as $usage) {

            if (!isset($usage[$contact->nameColumn])) {
                $usage[$contact->nameColumn] = 'n/a';
            }

            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('contact.php?contact_id=' . $usage['contact_id'], $usage[$contact->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }
        EaseContainer::addItemCustom($listing, $usedBy);
        $infopanel->addItem($usedBy);
    }
}

$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new \Ease\TWB\Panel(_('Příkaz') . ' <strong>' . $command->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);




$oPage->draw();
