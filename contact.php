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

$OPage->onlyForLogged();

$Contact = new IEContact($OPage->getRequestValue('contact_id', 'int'));

$autoCreate = $OPage->getRequestValue('autocreate');
if($autoCreate == 'default'){
    $Contact->setData(IEContact::ownContactData() );
    $ContactID = $Contact->saveToMySQL();
}

if ($OPage->isPosted()) {
    $Contact->takeData($_POST);
    $ContactID = $Contact->saveToMySQL();
    if (is_null($ContactID)) {
        $OUser->addStatusMessage(_('Kontakt nebyl uložen'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Kontakt byl uložen'), 'success');
    }
}

    $Contact->saveMembers();

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Contact->delete();
}


$OPage->addItem(new IEPageTop(_('Editace kontaktu') . ' ' . $Contact->getName()));



$ContactEdit = new IECfgEditor($Contact);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Contact', 'contact.php', 'POST', $ContactEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Contact->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Contact->getMyKeyColumn(), $Contact->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$OPage->column3->addItem($Contact->deleteButton());
$OPage->AddCss('
input.ui-button { width: 100%; }
');
if ($Contact->getId()) {
    $OPage->column1->addItem($Contact->ownerLinkButton());
}

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
