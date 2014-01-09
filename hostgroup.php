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

$hostgroup = new IEHostgroup($oPage->getRequestValue('hostgroup_id', 'int'));

if ($oPage->isPosted()) {
    $hostgroup->takeData($_POST);
    
    if(!$hostgroup->getId()){
        $hostgroup->setDataValue('members', array());
    }
    
    $hostgroupID = $hostgroup->saveToMySQL();
    if (is_null($hostgroupID)) {
        $oUser->addStatusMessage(_('Skupina hostů nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina hostů byla uložena'), 'success');
    }
}

$hostgroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $hostgroup->delete();
}

$oPage->addItem(new IEPageTop(_('Editace skupiny hostů') . ' ' . $hostgroup->getName()));

$HostgroupEdit = new IECfgEditor($hostgroup);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Hostgroup', 'hostgroup.php', 'POST', $HostgroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($hostgroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($hostgroup->getmyKeyColumn(), $hostgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($hostgroup->deleteButton());

if ($hostgroup->getId()) {
    $oPage->columnI->addItem($hostgroup->ownerLinkButton());
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
