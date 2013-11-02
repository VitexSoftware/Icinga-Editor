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

$OPage->onlyForLogged();

$Servicegroup = new IEServicegroup($OPage->getRequestValue('servicegroup_id', 'int'));

if ($OPage->isPosted()) {
    $Servicegroup->takeData($_POST);
    $ServicegroupID = $Servicegroup->saveToMySQL();
    if (is_null($ServicegroupID)) {
        $OUser->addStatusMessage(_('Skupina služeb nebyla uložena'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Skupina služeb byla uložena'), 'success');
    }
}


$Servicegroup->saveMembers();

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Servicegroup->delete();
}

$OPage->addItem(new IEPageTop(_('Editace skupiny služeb') . ' ' . $Servicegroup->getName()));

$ServicegroupEdit = new IECfgEditor($Servicegroup);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Servicegroup', 'servicegroup.php', 'POST', $ServicegroupEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Servicegroup->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Servicegroup->getMyKeyColumn(), $Servicegroup->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
