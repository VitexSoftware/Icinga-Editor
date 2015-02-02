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
require_once 'classes/IEPassiveCheckedServiceForm.php';
require_once 'classes/IEServicegroup.php';

$oPage->onlyForLogged();

$serviceName = trim($oPage->getRequestValue('service_name'));
$platform = trim($oPage->getRequestValue('platform'));
$service = new IEService();
$service->owner = &$oUser;

if ($serviceName) {

    $service->setData(
        array(
          $service->userColumn => $oUser->getUserID(),
          'service_description' => $serviceName,
          'use' => 'generic-service',
          'platform' => 'generic',
          'register' => true,
          'generate' => TRUE,
          'platform' => $platform,
          'display_name' => $serviceName,
          'passive_checks_enabled' => true
        )
    );

    if ($service->saveToMysql()) {
        /*
          $serviceGroup = new IEServiceGroup;
          if ($serviceGroup->loadDefault()) {
          $serviceGroup->setDataValue($serviceGroup->nameColumn, EaseShared::user()->getUserLogin());
          $serviceGroup->addMember('members', $service->getId(), $service->getName());
          $serviceGroup->saveToMySQL();
          }
         */
        $oPage->redirect('service.php?service_id=' . $service->getId());
        exit();
    }
} else {
    if ($oPage->isPosted()) {
        $oPage->addStatusMessage(_('Prosím zastejte název sledovaného servicea'), 'warning');
    }
}




$oPage->addItem(new IEPageTop(_('Průvodce založením služby')));

//$oPage->columnI->addItem(
//    new EaseTWBPanel(_('Volba druhu servicea'), 'success', _('Aktivni '))
//);
//$oPage->columnIII->addItem(
//    new EaseTWBPanel(_('Volba druhu servicea'), 'info', _('Pasivní '))
//);



$oPage->columnII->addItem(new IEPassiveCheckedServiceForm('passive'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
