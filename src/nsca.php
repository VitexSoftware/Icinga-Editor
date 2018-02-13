<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @deprecated since version 222
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->onlyForLogged();

$hostId = $oPage->getRequestValue('host_id', 'int');
$host   = new Engine\Host($hostId);

$preferences = new Preferences;
$prefs       = $preferences->getPrefs();

$nscabat = '
set NSCLIENT="%ProgramFiles%\NSClient++\nscp.exe"
%NSCLIENT% service --stop

del "%ProgramFiles%\NSClient++\nsclient.ini"
%NSCLIENT% settings --generate --add-defaults --load-all

%NSCLIENT% settings --path "/modules" --key Scheduler --set enabled
%NSCLIENT% settings --path "/modules" --key CheckDisk --set enabled
%NSCLIENT% settings --path "/modules" --key CheckEventLog --set enabled
%NSCLIENT% settings --path "/modules" --key CheckExternalScripts --set enabled
%NSCLIENT% settings --path "/modules" --key CheckHelpers --set enabled
%NSCLIENT% settings --path "/modules" --key CheckNSCP --set enabled
%NSCLIENT% settings --path "/modules" --key CheckSystem --set enabled
%NSCLIENT% settings --path "/modules" --key CheckWMI --set enabled
%NSCLIENT% settings --path "/modules" --key NSCAClient --set enabled

%NSCLIENT% settings --path /settings/NSCA/client --key hostname --set '.$host->getName().'
%NSCLIENT% settings --path /settings/NSCA/client --key channel --set NSCA
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key "allowed ciphers" --set "ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH"
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key encryption --set 3des
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key password --set '.$prefs['nscapassword'].'
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key address --set '.$prefs['serverip'].'
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key port --set 5667
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key timeout --set 30
%NSCLIENT% settings --path /settings/scheduler/schedules/default --key interval --set 60s
';


$hostName = $host->getName();
$service  = new Engine\Service();


$host_passive = (boolean) $host->getDataValue('passive_checks_enabled');
if (!$host_passive) {
    die(_('Host neni konfigurovan pro pasivni checky'));
}

$servicesAssigned = $service->dblink->queryToArray('SELECT '.$service->keyColumn.','.$service->nameColumn.',`use` FROM '.$service->myTable.' WHERE host_name LIKE \'%"'.$host->getName().'"%\'',
    $service->keyColumn);


$allServices = $service->getListing(
    null, true,
    [
        'platform', 'check_command-remote', 'check_command-params', 'passive_checks_enabled',
        'active_checks_enabled', 'use', 'check_interval', 'check_command-remote'
    ]
);

foreach ($allServices as $serviceID => $serviceInfo) {
    $servicePassive = (boolean) $serviceInfo['passive_checks_enabled'];
    $serviceActive  = (boolean) $serviceInfo['active_checks_enabled'];
    if ($serviceInfo['register'] != 1) {
        unset($allServices[$serviceID]);
        continue;
    }

    if (($serviceInfo['platform'] != 'generic') && $serviceInfo['platform'] != $host->getDataValue('platform')) {
        unset($allServices[$serviceID]);
        continue;
    }

    if (!$servicePassive) {
        unset($allServices[$serviceID]);
        continue;
    }
}


/* Naplní hodnoty z předloh */
$usedCache     = [];
$commandsCache = [];
foreach ($allServices as $rowId => $service) {
    if (isset($service['use'])) {
        $remote = $service['check_command-remote'];
        if (!isset($commandsCache[$remote])) {
            $command                = new Engine\Command($remote);
            $commandsCache[$remote] = $command->getData();
        }
    }

    if (isset($service['use'])) {
        $use = $service['use'];

        if (!isset($usedCache[$use])) {
            $used = new Engine\Service;
            $used->setKeyColumn('name');
            if ($used->loadFromSQL($use)) {
                $used->resetObjectIdentity();
                $usedCache[$use] = $used->getData();
            }
        }
        if (isset($usedCache[$use])) {
            foreach ($usedCache[$use] as $templateKey => $templateValue) {
                if ($templateKey != 'check_interval') {
                    continue;
                }
                if (!is_null($templateValue) && !isset($allServices[$rowId][$templateKey])) {
                    $allServices[$rowId][$templateKey] = $templateValue;
                }
            }
        }
    }
}


$intervals = [];
foreach ($allServices as $rowId => $service) {
    $intervals[$service['check_interval']][] = $rowId;
}



foreach ($intervals as $interval => $members) {
    $nscabat .= '
%NSCLIENT% settings --path "/settings/scheduler/schedules/sch'.$interval.'" --key channel --set NSCA
%NSCLIENT% settings --path "/settings/scheduler/schedules/sch'.$interval.'" --key interval --set '.$interval.'s
%NSCLIENT% settings --path "/settings/scheduler/schedules/sch'.$interval.'" --key report --set all
%NSCLIENT% settings --path "/settings/scheduler/schedules/sch'.$interval.'" --key "is template" --set true
    ';


    foreach ($allServices as $serviceId => $service) {
        if (!in_array($service['service_id'], $members)) {
            continue;
        }
        $serviceName   = $service['service_description'];
        $serviceCmd    = $service['check_command-remote'];
        $serviceParams = $service['check_command-params'];
        $nscabat       .= "\nREM #".$service['service_id'].' '.$serviceName."\n";

        if (isset($commandsCache[$serviceCmd])) {
            $cmdline = $commandsCache[$serviceCmd]['command_line'];
        } else {
            $cmdline = $serviceCmd;
        }

        if (strstr($cmdline, 'scripts\\')) {
            $nscabat .= '%NSCLIENT% settings --path "/settings/external scripts/wrapped scripts" --key "'.str_replace(' ',
                    '_', $serviceName).'" --set "'.
                $cmdline.' '.$serviceParams."\"\n";
        } else {
            $nscabat .= '%NSCLIENT% settings --path "/settings/external scripts/alias" --key "'.str_replace(' ',
                    '_', $serviceName).'" --set "'.$cmdline.' '.$serviceParams."\"\n";
        }
        $nscabat .= '%NSCLIENT% settings --path "/settings/scheduler/schedules/'.str_replace(' ',
                '_', $serviceName).'-'.$oUser->getUserLogin().'" --key command --set "'.str_replace(' ',
                '_', $serviceName)."\"\n";
        $nscabat .= '%NSCLIENT% settings --path "/settings/scheduler/schedules/'.str_replace(' ',
                '_', $serviceName).'-'.$oUser->getUserLogin().'" --key parent --set "sch'.$service['check_interval']."\"\n";
    }
}




$nscabat .= '
%NSCLIENT% test
%NSCLIENT% service --start
';




if ($host->getDataValue('passive_checks_enabled')) {
    if ($host->getDataValue('platform') == 'windows') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$host->getName().'_nsca.bat');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.strlen($nscabat));
        echo str_replace("\n", "\r\n", $nscabat);
    }
}

