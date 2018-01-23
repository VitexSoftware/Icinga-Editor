<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Contact editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contact = new Engine\Contact($oPage->getRequestValue('contact_id', 'int'));

if ($oPage->isPosted()) {
    $contact->takeData($_POST);
    $contactID = $contact->saveToSQL();
    if (is_null($contactID)) {
        $oUser->addStatusMessage(_('Contact was not saved'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Contact was saved'), 'success');
    }
}

$contact->saveMembers();

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $contact->delete();
    $oPage->redirect('contacts.php');
    exit();
}

$oPage->addItem(new UI\PageTop(_('Contact Editor') . ' ' . $contact->getName()));

switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new \Ease\Container;
        $form->addItem(new \Ease\Html\H2Tag($contact->getName()));
        $confirmator = $form->addItem(new \Ease\TWB\Panel(_('Are you sure ?')), 'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('?' . $contact->keyColumn . '=' . $contact->getID(), _('No') . ' ' . \Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&' . $contact->keyColumn . '=' . $contact->getID(), _('Yes') . ' ' . \Ease\TWB\Part::glyphIcon('remove'), 'danger'));
        break;
    default :


        $contactEdit = new UI\CfgEditor($contact);

        $form = new \Ease\Html\Form('Contact', 'contact.php', 'POST', $contactEdit, ['class' => 'form-horizontal']);
        $form->setTagID($form->getTagName());
        if (!is_null($contact->getMyKey())) {
            $form->addItem(new \Ease\Html\InputHiddenTag($contact->getKeyColumn(), $contact->getMyKey()));
        }
        $form->addItem('<br>');
        $form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));
        break;
}



$service = new Engine\Service;
$serviceUsages = $service->getColumnsFromSQL([$service->getMyKeyColumn(), $service->nameColumn], ['contacts' => '%' . $contact->getName() . '%'], $service->nameColumn, $service->getMyKeyColumn());



$oPage->addItem(new UI\PageBottom());

$infopanel = new UI\InfoBox($contact);
$tools = new \Ease\TWB\Panel(_('Tools'), 'warning');
if ($contact->getId()) {
    $tools->addItem($contact->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning', $contact->transferForm()));



    if (count($serviceUsages)) {
        $usedBy = new \Ease\TWB\Panel(_('Used by services'));
        $listing = $usedBy->addItem(new \Ease\Html\UlTag(null, ['class' => 'list-group']));
        foreach ($serviceUsages as $usage) {
            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('service.php?service_id=' . $usage['service_id'], $usage[$service->nameColumn])
                , ['class' => 'list-group-item'])
            );
        }
        $infopanel->addItem($usedBy);
    }

    $host = new Engine\Host;
    $hostUsages = $host->getColumnsFromSQL([$host->getMyKeyColumn(), $host->nameColumn], ['contacts' => '%' . $contact->getName() . '%'], $host->nameColumn, $host->getMyKeyColumn());

    if (count($hostUsages)) {
        $usedBy = new \Ease\TWB\Panel(_('Used by hosts'));
        $listing = $usedBy->addItem(new \Ease\Html\UlTag(null, ['class' => 'list-group']));
        foreach ($hostUsages as $usage) {
            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('host.php?host_id=' . $usage['host_id'], $usage[$host->nameColumn])
                , ['class' => 'list-group-item'])
            );
        }
        $infopanel->addItem($usedBy);
    }
}

$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new \Ease\TWB\Panel(_('Contact') . ' <strong>' . $contact->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
