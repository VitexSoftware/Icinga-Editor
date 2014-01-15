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
require_once 'classes/IEServiceTweaker.php';

$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));
$host = new IEHost($oPage->getRequestValue('host_id', 'int'));


$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $service->delete();
    $oPage->redirect('host.php?host_id='.$host->getId());
    exit();
}


if ($service->getOwnerID() != $oUser->getMyKey() ) {
    $service->delMember('host_name', $host->getId(), $host->getName());
    $service->setDataValue('action_url', $_SERVER['REQUEST_URI']);
    $service->setDataValue('parent_id', $service->getId());
    $service->saveToMySQL();

    $service->unsetDataValue($service->getmyKeyColumn());
    $service->setDataValue('public', 0);

    $service->setDataValue($service->userColumn, $oUser->getId());
    $service->setDataValue($service->nameColumn, $service->getName() . ' ' . $host->getName());
    $service->setDataValue('host_name', array());
    $service->addMember('host_name', $host->getId(), $host->getName());
    if ($service->saveToMySQL()) {
        $oUser->addStatusMessage(_('Služba byla odvozena'), 'success');
    } else {
        $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
    }

}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(IEHostOverview::platformIcon($service->getDataValue('platform')),$service->getName())));

$oPage->columnII->addItem(new IEServiceTweaker($service,$host));

$oPage->columnIII->addItem($service->deleteButton($service->getName(),'host_id='.$host->getId()));


$oPage->addItem(new IEPageBottom());

$oPage->draw();
