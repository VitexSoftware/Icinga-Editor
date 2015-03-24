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
$oPage->addPageColumns();

$contactEdit = new IECfgEditor($contact);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Contact', 'contact.php', 'POST', $contactEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($contact->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($contact->getmyKeyColumn(), $contact->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->columnIII->addItem($contact->deleteButton());
$oPage->AddCss('
input.ui-button { width: 100%; }
');
if ($contact->getId()) {
    $oPage->columnI->addItem($contact->ownerLinkButton());
}


$service = new IEService;
$serviceUsages = $service->getColumnsFromMySQL(array($service->getMyKeyColumn(), $service->nameColumn), array('contacts' => '%' . $contact->getName() . '%'), $service->nameColumn, $service->getMyKeyColumn());


if (count($serviceUsages)) {
    $usedBy = new EaseTWBPanel(_('Používající služby'));
    $listing = $usedBy->addItem(new EaseHtmlUlTag(null, array('class' => 'list-group')));
    foreach ($serviceUsages as $usage) {
        $listing->addItem(
            new EaseHtmlLiTag(
            new EaseHtmlATag('service.php?service_id=' . $usage['service_id'], $usage[$service->nameColumn])
            , array('class' => 'list-group-item'))
        );
    }
    $form = $oPage->columnI->addItem($usedBy);
}

$host = new IEHost;
$hostUsages = $host->getColumnsFromMySQL(array($host->getMyKeyColumn(), $host->nameColumn), array('contacts' => '%' . $contact->getName() . '%'), $host->nameColumn, $host->getMyKeyColumn());

if (count($hostUsages)) {
    $usedBy = new EaseTWBPanel(_('Používající hosty'));
    $listing = $usedBy->addItem(new EaseHtmlUlTag(null, array('class' => 'list-group')));
    foreach ($hostUsages as $usage) {
        $listing->addItem(
            new EaseHtmlLiTag(
            new EaseHtmlATag('host.php?host_id=' . $usage['host_id'], $usage[$host->nameColumn])
            , array('class' => 'list-group-item'))
        );
    }
    $form = $oPage->columnI->addItem($usedBy);
}






$oPage->addItem(new IEPageBottom());

$oPage->draw();
