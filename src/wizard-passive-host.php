<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostName    = trim($oPage->getRequestValue('host_name'));
$platform    = trim($oPage->getRequestValue('platform'));
$host_group     = $oPage->getRequestValue('host_group', 'int');
$host_is_server = $oPage->getRequestValue('host_is_server', 'boolean');
$host        = new Engine\Host();
$host->owner = &$oUser;

if ($hostName && $platform) {

    $host->setData(
        [
            $host->userColumn => $oUser->getUserID(),
            'host_name' => $hostName,
            'use' => 'generic-host',
            'platform' => 'generic',
            'register' => true,
            'generate' => TRUE,
            'platform' => $platform,
            'alias' => $hostName,
            'host_is_server' => $host_is_server,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1,
            'check_freshness' => 1,
            'freshness_threshold' => 900, // 15m.
            'flap_detection_enabled' => 0,
            'check_command' => 'return-unknown'
        ]
    );

    if ($host_group) {
        $hostgroup = new Engine\Hostgroup($host_group);
        $host->addMember('hostgroups', $hostgroup->getId(),
            $hostgroup->getName());
        $hostgroup->addMember('members', $host->getId(), $host->getName());
        $hostgroup->saveToSQL();
    }


    if ($host->saveToSQL()) {

        $hostGroup = new Engine\Hostgroup();
        if ($hostGroup->loadDefault()) {
            $hostGroup->setDataValue($hostGroup->nameColumn,
                \Ease\Shared::user()->getUserLogin());
            $hostGroup->addMember('members', $host->getId(), $host->getName());
            $hostGroup->saveToSQL();
            $host->addMember('hostgroups', $hostGroup->getId(),
                $hostGroup->getName());
            $host->saveToSQL();
        }

        $oPage->redirect('host.php?host_id='.$host->getId());
        exit();
    }
} else {
    if ($oPage->isPosted()) {
        $oPage->addStatusMessage(_('Please enter name of Host to watch'),
            'warning');
    }
}




$oPage->addItem(new UI\PageTop(_('New Passive Host wizard')));

$oPage->container->addItem(new \Ease\TWB\Panel(_('New Passive Host'), 'info', new UI\PassiveCheckedHostForm('passive')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
