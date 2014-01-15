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
require_once 'classes/IEHostSelector.php';

$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));
$host = new IEHost($oPage->getRequestValue('host_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($service->rename($newname)) {
                $oUser->addStatusMessage(_('Služba byla přejmenována'), 'success');
            } else {
                $oUser->addStatusMessage(_('Sužba nebyla přejmenována'), 'warning');
            }
        }
        break;
}

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $service->delete();
    $oPage->redirect('host.php?host_id=' . $host->getId());
    exit();
}

if ($service->getOwnerID() != $oUser->getMyKey()) {
    $service->delMember('host_name', $host->getId(), $host->getName());
    $service->saveToMySQL();

    $service->unsetDataValue($service->getmyKeyColumn());
    $service->setDataValue('public', 0);
    $service->unsetDataValue('tcp_port');
    $service->setDataValue('action_url', $_SERVER['REQUEST_URI']);
    $service->setDataValue('parent_id', $service->getId());
    $service->setDataValue($service->userColumn, $oUser->getId());

    $newname = $service->getName() . ' ' . $host->getName();

    $servcount = $service->myDbLink->queryToCount('SELECT ' . $service->getmyKeyColumn() . ' FROM ' . $service->myTable . ' WHERE ' . $service->nameColumn . ' LIKE \'' . $newname . '%\' ');

    if ($servcount) {
        $newname .= ' ' . ($servcount + 1);
    }

    $service->setDataValue($service->nameColumn, $newname);
    $service->setDataValue('host_name', array());
    $service->addMember('host_name', $host->getId(), $host->getName());

    if ($service->saveToMySQL()) {
        $oUser->addStatusMessage(_('Služba byla odvozena'), 'success');
    } else {
        $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
    }
}

$delhost = $oPage->getGetValue('delhost');
if ($delhost) {
    $service->delMember('host_name', $oPage->getGetValue('host_id', 'int'), $delhost );
}

$addhost = $oPage->getGetValue('addhost');
if ($addhost) {
    $service->addMember('host_name', $oPage->getGetValue('host_id', 'int'), $addhost );
}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(IEHostOverview::platformIcon($service->getDataValue('platform')), $service->getName())));

$oPage->columnII->addItem(new IEServiceTweaker($service, $host));

$oPage->columnIII->addItem($service->deleteButton($service->getName(), 'host_id=' . $host->getId()));

$renameForm = new EaseTWBForm('Rename', '?action=rename&amp;host_id=' . $host->getID() . '&service_id=' . $service->getId());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $service->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new EaseHtmlFieldSet(_('Přejmenování'), $renameForm));

$oPage->columnI->addItem(new IEHostSelector($service));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
