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

$autoCreate = $oPage->getRequestValue('autocreate');
if($autoCreate == 'default'){
    $contact->setData(IEContact::ownContactData() );
    $contactID = $contact->saveToMySQL();
}

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



$contactEdit = new IECfgEditor($contact);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Contact', 'contact.php', 'POST', $contactEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($contact->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($contact->getmyKeyColumn(), $contact->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$oPage->columnIII->addItem($contact->deleteButton());
$oPage->AddCss('
input.ui-button { width: 100%; }
');
if ($contact->getId()) {
    $oPage->columnI->addItem($contact->ownerLinkButton());
}

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
