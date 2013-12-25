<?php

/**
 * Icinga Editor - skupina služeb
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEServicegroup.php';
require_once 'classes/IECfgEditor.php';

$oPage->onlyForLogged();

$Servicegroup = new IEServicegroup($oPage->getRequestValue('servicegroup_id', 'int'));

if ($oPage->isPosted()) {
    $Servicegroup->takeData($_POST);
    $ServicegroupID = $Servicegroup->saveToMySQL();
    if (is_null($ServicegroupID)) {
        $oUser->addStatusMessage(_('Skupina služeb nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina služeb byla uložena'), 'success');
    }
}


$Servicegroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $Servicegroup->delete();
}

$oPage->addItem(new IEPageTop(_('Editace skupiny služeb') . ' ' . $Servicegroup->getName()));

$ServicegroupEdit = new IECfgEditor($Servicegroup);

$form = $oPage->column2->addItem(new EaseHtmlForm('Servicegroup', 'servicegroup.php', 'POST', $ServicegroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Servicegroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($Servicegroup->getMyKeyColumn(), $Servicegroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
