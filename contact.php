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

$Contact = new IEContact($oPage->getRequestValue('contact_id', 'int'));

$autoCreate = $oPage->getRequestValue('autocreate');
if($autoCreate == 'default'){
    $Contact->setData(IEContact::ownContactData() );
    $ContactID = $Contact->saveToMySQL();
}

if ($oPage->isPosted()) {
    $Contact->takeData($_POST);
    $ContactID = $Contact->saveToMySQL();
    if (is_null($ContactID)) {
        $oUser->addStatusMessage(_('Kontakt nebyl uložen'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Kontakt byl uložen'), 'success');
    }
}

    $Contact->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $Contact->delete();
}


$oPage->addItem(new IEPageTop(_('Editace kontaktu') . ' ' . $Contact->getName()));



$ContactEdit = new IECfgEditor($Contact);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Contact', 'contact.php', 'POST', $ContactEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Contact->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($Contact->getMyKeyColumn(), $Contact->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$oPage->columnIII->addItem($Contact->deleteButton());
$oPage->AddCss('
input.ui-button { width: 100%; }
');
if ($Contact->getId()) {
    $oPage->columnI->addItem($Contact->ownerLinkButton());
}

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
