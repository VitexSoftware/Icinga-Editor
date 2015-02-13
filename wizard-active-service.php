<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEServiceWizardForm.php';
require_once 'classes/IEServicegroup.php';

$oPage->onlyForLogged();

$serviceId = $oPage->getRequestValue('service_id', 'int');
$serviceName = trim($oPage->getRequestValue('service_name'));
$remoteCmd = trim($oPage->getRequestValue('check_command-remote'));
$remoteCmdParam = trim($oPage->getRequestValue('check_command-params'));
$platform = trim($oPage->getRequestValue('platform'));
$service = new IEService($serviceId);
$service->owner = &$oUser;

if (isset($platform)) {
    $service->setDataValue('platform', $platform);
}


if ($serviceName) {

    $data = array(
      $service->userColumn => $oUser->getUserID(),
      'service_description' => $serviceName,
      'use' => 'generic-service',
      'register' => true,
      'generate' => true,
      'display_name' => $serviceName,
      'passive_checks_enabled' => 0,
      'active_checks_enabled' => 1
    );

    if (isset($remoteCmd)) {
        $data['check_command-remote'] = $remoteCmd;
    }

    if (isset($remoteCmdParam)) {
        $data['check_command-params'] = $remoteCmdParam;
    }

    $service->setData($data);

    if ($service->saveToMysql()) {
        /*
          $serviceGroup = new IEServiceGroup;
          if ($serviceGroup->loadDefault()) {
          $serviceGroup->setDataValue($serviceGroup->nameColumn, EaseShared::user()->getUserLogin());
          $serviceGroup->addMember('members', $service->getId(), $service->getName());
          $serviceGroup->saveToMySQL();
          }
         */
        if (strlen(trim($service->getDataValue('check_command-remote')))) {
            $oPage->addStatusMessage(_('Služba byla založena'), 'success');
            $oPage->redirect('service.php?service_id=' . $service->getId());
            exit();
        } else {
            $oPage->addStatusMessage(_('Není zvolen vzdálený příkaz testu'), 'warning');
        }
    }
} else {
    if ($oPage->isPosted()) {
        $oPage->addStatusMessage(_('Prosím zastejte název služby'), 'warning');
    }
}


$oPage->addItem(new IEPageTop(_('Průvodce založením pasivně sledované služby')));

$oPage->columnI->addItem(
    new EaseTWBPanel(_('Aktivní checky'), 'info', _('senzor (nrpe/nscp.exe) běží na vzdáleném hostu, a výsledky nadefinovaných testů zasílá protokolem NRPE na monitorovací server.'))
);
$oPage->columnIII->addItem(
    new EaseTWBPanel(_('Aktivně sledovaná služba'), 'info', _('Nabízené příkazy jsou definovány jako vzdálené a odpovídající zvolené platformě. Parametry záleží na konkrétně zvoleném příkazu testu.'))
);


$oPage->columnII->addItem(new IEServiceWizardForm($service));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
