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

$OPage->onlyForLogged();

$Hostgroup = new IEHostgroup($OPage->getRequestValue('hostgroup_id', 'int'));

if ($OPage->isPosted()) {
    $Hostgroup->takeData($_POST);
    $HostgroupID = $Hostgroup->saveToMySQL();
    if (is_null($HostgroupID)) {
        $OUser->addStatusMessage(_('Skupina hostů nebyla uložena'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Skupina hostů byla uložena'), 'success');
    }
}

$Hostgroup->saveMembers();

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Hostgroup->delete();
}


$OPage->addItem(new IEPageTop(_('Editace skupiny hostů') . ' ' . $Hostgroup->getName()));

$HostgroupEdit = new IECfgEditor($Hostgroup);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Hostgroup', 'hostgroup.php', 'POST', $HostgroupEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Hostgroup->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Hostgroup->getMyKeyColumn(), $Hostgroup->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$OPage->AddCss('
input.ui-button { width: 100%; }
');

$OPage->column3->addItem($Hostgroup->deleteButton());

if ($Hostgroup->getId()) {
    $OPage->column1->addItem($Hostgroup->ownerLinkButton());
}


$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
