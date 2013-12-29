<?php

/**
 * Icinga Editor - skupina hostů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHostgroup.php';
require_once 'classes/IECfgEditor.php';

$oPage->onlyForLogged();

$Hostgroup = new IEHostgroup($oPage->getRequestValue('hostgroup_id', 'int'));

if ($oPage->isPosted()) {
    $Hostgroup->takeData($_POST);
    $HostgroupID = $Hostgroup->saveToMySQL();
    if (is_null($HostgroupID)) {
        $oUser->addStatusMessage(_('Skupina hostů nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina hostů byla uložena'), 'success');
    }
}

$Hostgroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $Hostgroup->delete();
}


$oPage->addItem(new IEPageTop(_('Editace skupiny hostů') . ' ' . $Hostgroup->getName()));

$HostgroupEdit = new IECfgEditor($Hostgroup);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Hostgroup', 'hostgroup.php', 'POST', $HostgroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Hostgroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($Hostgroup->getMyKeyColumn(), $Hostgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($Hostgroup->deleteButton());

if ($Hostgroup->getId()) {
    $oPage->columnI->addItem($Hostgroup->ownerLinkButton());
}


$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
