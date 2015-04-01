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

$servicegroupEdit = new IECfgEditor($serviceGroup);

$form = new EaseHtmlForm('Servicegroup', 'servicegroup.php', 'POST', $servicegroupEdit, array('class' => 'form-horizontal'));
$form->setTagID($form->getTagName());
if (!is_null($serviceGroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($serviceGroup->getmyKeyColumn(), $serviceGroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->addItem(new IEPageBottom());

$infopanel = new IEInfoBox($serviceGroup);
$tools = new EaseTWBPanel(_('Nástroje'), 'warning');
if ($serviceGroup->getId()) {
    $tools->addItem($serviceGroup->deleteButton());
    $tools->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $serviceGroup->transferForm()));
}
$pageRow = new EaseTWBRow;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new EaseTWBPanel(_('Příkaz') . ' <strong>' . $serviceGroup->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);




$oPage->draw();
