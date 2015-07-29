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

$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));
$host = new IEHost($oPage->getRequestValue('host_id', 'int'));

switch ($oPage->getRequestValue('action')) {

    case 'clone':
        $service->setDataValue('parent_id', $service->getId());
        $service->unsetDataValue($service->getmyKeyColumn());
        $service->setDataValue('host_name', array($host->getId() => $host->getName()));
        $service->setDataValue('hostgroup_name', array());
        $service->setDataValue('user_id', $oUser->getID());
        $service->setDataValue($service->nameColumn, _('Klon') . ' ' . $service->getName());
        if ($service->saveToMySQL()) {
            $oUser->addStatusMessage(_('Služba byla naklonována'), 'success');
            $oPage->redirect('servicetweak.php?service_id=' . $service->getId() . '&host_id=' . $host->getId());
        } else {
            $oUser->addStatusMessage(_('Sužba nebyla naklonována'), 'warning');
        }
        break;
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
    if ($service->fork($host)) {
        $oUser->addStatusMessage(_('Služba byla odvozena'), 'success');
    } else {
        $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
    }
}

$delhost = $oPage->getGetValue('delhost');
if ($delhost) {
    $service->delMember(
        'host_name', $oPage->getGetValue('host_id', 'int'), $delhost
    );
    $service->saveToMySql();
}

$addhost = $oPage->getGetValue('addhost');
if ($addhost) {
    $service->addMember(
        'host_name', $oPage->getGetValue('host_id', 'int'), $addhost
    );
    $service->saveToMySql();
}

$delcnt = $oPage->getGetValue('delcontact');
if ($delcnt) {
    $service->delMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $delcnt
    );
    $service->saveToMySql();
}

$addcnt = $oPage->getGetValue('addcontact');
if ($addcnt) {
    $service->addMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $addcnt
    );
    $service->saveToMySql();
}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));
$oPage->addPageColumns();

$serviceTweak = new IEServiceTweaker($service, $host);

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(new IEPlatformIcon($service->getDataValue('platform')), $service->getName())));

$oPage->columnII->addItem($serviceTweak);

$oPage->columnIII->addItem($service->deleteButton($service->getName(), 'host_id=' . $host->getId()));

$oPage->columnIII->addItem(new EaseTWBLinkButton('service.php?service_id=' . $service->getID(), _('Editace služby') . ' ' . $service->getName()));

$renameForm = new EaseTWBForm('Rename', '?action=rename&amp;host_id=' . $host->getID() . '&service_id=' . $service->getId());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $service->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new EaseTWBPanel(_('Přejmenování'), 'info', $renameForm));

$oPage->columnIII->addItem($service->cloneButton());


$oPage->columnI->addItem(new IEHostSelector($service));
$oPage->columnI->addItem(new IEContactSelector($service));

$oPage->columnIII->addItem(new EaseTWBLinkButton('host.php?host_id=' . $host->getId(), array(_('Zpět na') . ' ', $host, ' ', $host->getName()), 'default'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
