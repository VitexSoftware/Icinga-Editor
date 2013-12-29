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

$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));

if ($oPage->getRequestValue('action') == 'clone') {
    $service->unsetDataValue($service->getMyKeyColumn());
    $service->setDataValue($service->NameColumn, $service->getName() . ' ' . _('Cloned'));
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

$serviceEdit = new IECfgEditor($service);

$oPage->columnII->addItem($serviceEdit);

$oPage->columnIII->addItem($service->deleteButton());
$oPage->columnIII->addItem($service->cloneButton());

if ($service->getId()) {
    $oPage->columnI->addItem($service->ownerLinkButton());
}
$oPage->addItem(new IEPageBottom());

$oPage->draw();
