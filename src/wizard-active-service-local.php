<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Local Active Service wizard
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$serviceId      = $oPage->getRequestValue('service_id', 'int');
$owner_id       = $oPage->getRequestValue('user_id', 'int');
$name           = trim($oPage->getRequestValue('name'));
$use            = trim($oPage->getRequestValue('use'));
$checkCmd       = trim($oPage->getRequestValue('check_command'));
$checkCmdParam  = trim($oPage->getRequestValue('check_command-params'));
$platform       = trim($oPage->getRequestValue('platform'));
$service        = new Engine\Service($serviceId);
$service->owner = &$oUser;

if (isset($platform)) {
    $service->setDataValue('platform', $platform);
}


if ($name) {

    if (!isset($use)) {
        $use = 'generic-service';
    }

    if (($owner_id == 0) || ($owner_id == 'null')) {
        $owner_id = null;
    }

    $data = [
        $service->userColumn => $owner_id,
        'use' => $use,
        'generate' => true,
        'active_checks_enabled' => 1,
        'passive_checks_enabled' => 0,
        'check_freshness' => 1,
        'freshness_threshold' => 900,
        'check_command' => $checkCmdParam
    ];

    if ($oPage->getRequestValue('autocfg') == 'on') {
        $data['autocfg'] = 1;
    } else {
        $data['autocfg'] = 0;
    }

    if ($oPage->getRequestValue('register', 'int')) {
        $data['service_description'] = $name;
        $data['display_name']        = $name;
        $data['register']            = true;
    } else {
        $data['name']     = $name;
        $data['register'] = false;
    }

    if ($oUser->getSettingValue('admin')) {
        $data['public'] = true;
    } else {
        $data['public'] = false;
    }

    if (isset($checkCmdParam)) {
        $data['check_command-params'] = $checkCmdParam;
    }

    $service->setData($data);

    if ($service->saveToSQL()) {
        /*
          $serviceGroup = new Engine\ServiceGroup;
          if ($serviceGroup->loadDefault()) {
          $serviceGroup->setDataValue($serviceGroup->nameColumn, \Ease\Shared::user()->getUserLogin());
          $serviceGroup->addMember('members', $service->getId(), $service->getName());
          $serviceGroup->saveToSQL();
          }
         */
        if (strlen(trim($service->getDataValue('check_command'))) && $data['register']) {
            $oPage->addStatusMessage(_('Service was created'), 'success');
            $oPage->redirect('service.php?service_id='.$service->getId());
            exit();
        } else {
            $oPage->addStatusMessage(_('Check command not specified'), 'warning');
        }
    }
} else {
    if ($oPage->isPosted()) {
        $oPage->addStatusMessage(_('Please enter servce name'), 'warning');
    } else {
        $service->setDataValue($service->userColumn, $oUser->getUserID());
    }
}


$oPage->addItem(new UI\PageTop(_('Loacal active service Wizard')));
$oPage->addPageColumns();

$oPage->columnI->addItem(
    new \Ease\TWB\Panel(_('Active check'), 'info',
    _('the sensor (nrpe / nscp.exe) runs on a remote host, and the results of the defined tests are sent by the NRPE protocol to the monitoring server.'))
);
$oPage->columnIII->addItem(
    new \Ease\TWB\Panel(_('Active watched service'), 'info',
    _('The commands offered are defined as remote and corresponding to the selected platform. Parameters depend on the particular test command you choose.'))
);


$oPage->columnII->addItem(new UI\ServiceWizardFormLocal($service));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
