<?php

/**
 * Icinga Editor - skupina kontaktů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContactgroup.php';
require_once 'classes/IECfgEditor.php';


$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Editace skupiny kontaktu')));

$Contactgroup = new IEContactgroup($oPage->getRequestValue('contactgroup_id', 'int'));

if ($oPage->isPosted()) {
    $Contactgroup->takeData($_POST);
    $ContactgroupID = $Contactgroup->saveToMySQL();
    if (is_null($ContactgroupID)) {
        $oUser->addStatusMessage(_('Skupina kontaktů nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina kontaktů byla uložena'), 'success');
    }
}

$Contactgroup->saveMembers();


$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $Contactgroup->delete();
}


$ContactgroupEdit = new IECfgEditor($Contactgroup);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Contactgroup', 'contactgroup.php', 'POST', $ContactgroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Contactgroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($Contactgroup->getmyKeyColumn(), $Contactgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
