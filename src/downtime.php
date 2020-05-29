<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Downtime or Uptime confirm
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

//downtime.php?host_id=23&state=start
//downtime.php?host_id=22&state=stop

$host_id = $oPage->getRequestValue('host_id', 'int');
$state = $oPage->getRequestValue('state');

if ($host_id && $state) {
    $host = new Engine\Host($host_id);
    $servicesAssigned = $host->getServices();
    $now = time();
    $extCmd = new ExternalCommand();
    switch ($state) {
        case 'start':
            $owner = new User($host->getOwnerID());
            $oneYear = 31556926; //In seconds
            $extCmd->addCommand('SCHEDULE_HOST_DOWNTIME;' . $host->getName() . ';' . $now . ';' . ($now + $oneYear) . ';0;0;' . $oneYear . ';' . $owner->getUserLogin() . ';remote downtime invoke');
            $extCmd->addCommand('DISABLE_HOST_NOTIFICATIONS;' . $host->getName());
            foreach ($servicesAssigned as $serviceDescription) {
                $extCmd->addCommand('DISABLE_SVC_NOTIFICATIONS;' . $host->getName() . ';' . $serviceDescription);
            }
            $extCmd->addCommand('PROCESS_HOST_CHECK_RESULT;' . $host->getName() . ';1;Host go Down');
            break;
        case 'stop':
            $extCmd->addCommand('PROCESS_HOST_CHECK_RESULT;' . $host->getName() . ';0;Host go Up');
            $extCmd->addCommand('DEL_DOWNTIME_BY_HOST_NAME;' . $host->getName());
            $extCmd->addCommand('ENABLE_HOST_NOTIFICATIONS;' . $host->getName());
            foreach ($servicesAssigned as $serviceDescription) {
                $extCmd->addCommand('ENABLE_SVC_NOTIFICATIONS;' . $host->getName() . ';' . $serviceDescription);
            }
            break;
        default :
            $oPage->addStatusMessage(sprintf(_('Unknown state %s.'), $state));
            die(_('State can be only start or stop'));
            break;
    }
    echo implode("<br/>\n", $extCmd->executeAll());
}