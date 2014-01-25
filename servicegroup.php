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

$serviceGroup = new IEServicegroup($oPage->getRequestValue('servicegroup_id', 'int'));

if ($oPage->isPosted()) {
    $serviceGroup->takeData($_POST);
    $ServicegroupID = $serviceGroup->saveToMySQL();
    if (is_null($ServicegroupID)) {
        $oUser->addStatusMessage(_('Skupina služeb nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina služeb byla uložena'), 'success');
    }
}


$serviceGroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $serviceGroup->delete();
}

$oPage->addItem(new IEPageTop(_('Editace skupiny služeb') . ' ' . $serviceGroup->getName()));

$ServicegroupEdit = new IECfgEditor($serviceGroup);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Servicegroup', 'servicegroup.php', 'POST', $ServicegroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($serviceGroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($serviceGroup->getmyKeyColumn(), $serviceGroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
