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


$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Editace skupiny kontaktu')));

$Contactgroup = new IEContactgroup($OPage->getRequestValue('contactgroup_id', 'int'));

if ($OPage->isPosted()) {
    $Contactgroup->takeData($_POST);
    $ContactgroupID = $Contactgroup->saveToMySQL();
    if (is_null($ContactgroupID)) {
        $OUser->addStatusMessage(_('Skupina kontaktů nebyla uložena'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Skupina kontaktů byla uložena'), 'success');
    }
}

$Contactgroup->saveMembers();


$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Contactgroup->delete();
}


$ContactgroupEdit = new IECfgEditor($Contactgroup);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Contactgroup', 'contactgroup.php', 'POST', $ContactgroupEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Contactgroup->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Contactgroup->getMyKeyColumn(), $Contactgroup->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
