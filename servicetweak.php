<?php

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEService.php';
require_once 'classes/IECfgEditor.php';
require_once 'classes/IEHostOverview.php';

$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));
$host = new IEHost($oPage->getRequestValue('host_id', 'int'));

if($service->getOwnerID() != $oUser->getMyKey() ){
    $service->delMember('host_name', $host->getId(), $host->getName());
    $service->saveToMySQL();
    
    $service->unsetDataValue($service->getmyKeyColumn());
    $service->setDataValue('public', 0);

    $service->setDataValue($service->userColumn,$oUser->getId());
    $service->setDataValue($service->nameColumn, $service->getName() . ' ' . $host->getName());
    $service->setDataValue('host_name',array());
    $service->addMember('host_name', $host->getId(), $host->getName());
    if ($service->saveToMySQL()) {
        $oUser->addStatusMessage(_('Služba byla zklonovana'), 'success');
    } else {
        $oUser->addStatusMessage(_('Služba nebyla zklonovana'), 'error');
    }
    
    
}





if ($oPage->isPosted()) {
    if ($oPage->getRequestValue('action') == 'clone') {
        $oUser->addStatusMessage(_('Služba byla zklonovana'), 'info');
        $service->unsetDataValue($service->getMyKey());
    } else {
        $service->takeData($_POST);
    }
    $serviceID = $service->saveToMySQL();
    if (is_null($serviceID)) {
        $oUser->addStatusMessage(_('Služba nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Služba byla uložena'), 'success');
    }
} else {
    $use = $oPage->getGetValue('use');
    if ($use) {
        $service->setDataValue('use', $use);
    }
}

$service->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $service->delete();
}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(IEHostOverview::platformIcon($service->getDataValue('platform')),$service->getName())));

$serviceEdit = new IECfgEditor($service);


$oPage->columnIII->addItem($service->deleteButton());
$oPage->columnIII->addItem($service->cloneButton());

if ($service->getId()) {
    $oPage->columnI->addItem($service->ownerLinkButton());
}



$form = $oPage->columnII->addItem(new EaseHtmlForm('Service', 'service.php', 'POST', $serviceEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($service->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($service->getMyKeyColumn(), $service->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');


$oPage->addItem(new IEPageBottom());

$oPage->draw();
