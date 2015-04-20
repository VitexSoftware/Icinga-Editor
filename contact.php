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
require_once 'classes/IEContact.php';
require_once 'classes/IECfgEditor.php';

$oPage->onlyForLogged();

$contact = new IEContact($oPage->getRequestValue('contact_id', 'int'));

if ($oPage->isPosted()) {
    $contact->takeData($_POST);
    $contactID = $contact->saveToMySQL();
    if (is_null($contactID)) {
        $oUser->addStatusMessage(_('Kontakt nebyl uložen'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Kontakt byl uložen'), 'success');
    }
}

$contact->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $contact->delete();
    $oPage->redirect('contacts.php');
    exit();
}

$oPage->addItem(new IEPageTop(_('Editace kontaktu') . ' ' . $contact->getName()));

switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form = new EaseContainer;
        $form->addItem(new EaseHtmlH2Tag($contact->getName()));
        $confirmator = $form->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new EaseTWBLinkButton('?' . $contact->myKeyColumn . '=' . $contact->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $contact->myKeyColumn . '=' . $contact->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));
        break;
    default :


        $contactEdit = new IECfgEditor($contact);

        $form = new EaseHtmlForm('Contact', 'contact.php', 'POST', $contactEdit, array('class' => 'form-horizontal'));
        $form->setTagID($form->getTagName());
        if (!is_null($contact->getMyKey())) {
            $form->addItem(new EaseHtmlInputHiddenTag($contact->getmyKeyColumn(), $contact->getMyKey()));
        }
        $form->addItem('<br>');
        $form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
        break;
}



$service = new IEService;
$serviceUsages = $service->getColumnsFromMySQL(array($service->getMyKeyColumn(), $service->nameColumn), array('contacts' => '%' . $contact->getName() . '%'), $service->nameColumn, $service->getMyKeyColumn());



$oPage->addItem(new IEPageBottom());

$infopanel = new IEInfoBox($contact);
$tools = new EaseTWBPanel(_('Nástroje'), 'warning');
if ($contact->getId()) {
    $tools->addItem($contact->deleteButton());
    $tools->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $contact->transferForm()));



    if (count($serviceUsages)) {
        $usedBy = new EaseTWBPanel(_('Používaný službami'));
        $listing = $usedBy->addItem(new EaseHtmlUlTag(null, array('class' => 'list-group')));
        foreach ($serviceUsages as $usage) {
            $listing->addItem(
                new EaseHtmlLiTag(
                new EaseHtmlATag('service.php?service_id=' . $usage['service_id'], $usage[$service->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }
        $infopanel->addItem($usedBy);
    }

    $host = new IEHost;
    $hostUsages = $host->getColumnsFromMySQL(array($host->getMyKeyColumn(), $host->nameColumn), array('contacts' => '%' . $contact->getName() . '%'), $host->nameColumn, $host->getMyKeyColumn());

    if (count($hostUsages)) {
        $usedBy = new EaseTWBPanel(_('Používaný hosty'));
        $listing = $usedBy->addItem(new EaseHtmlUlTag(null, array('class' => 'list-group')));
        foreach ($hostUsages as $usage) {
            $listing->addItem(
                new EaseHtmlLiTag(
                new EaseHtmlATag('host.php?host_id=' . $usage['host_id'], $usage[$host->nameColumn])
                , array('class' => 'list-group-item'))
            );
        }
        $infopanel->addItem($usedBy);
    }
}

$pageRow = new EaseTWBRow;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new EaseTWBPanel(_('Příkaz') . ' <strong>' . $contact->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
