<?php

require_once 'classes/IECommand.php';
require_once 'classes/IEPreferences.php';

/**
 * Description of NSCPConfigGenerator
 *
 * @author vitex
 */
class IENSCPConfigGenerator extends EaseAtom
{

    /**
     * Objekt hostu
     * @var IEHost
     */
    public $host = null;

    /**
     * Pole nastavení
     * @var  Array
     */
    public $prefs = null;

    /**
     * Pole konfiguračních fragmentů
     * @var array
     */
    public $nscBatArray = array();

    /**
     * Aktivni režim
     * @var boolean
     */
    public $hostActiveMode = false;

    /**
     * Pasivní režim
     * @var boolean
     */
    public $hostPassiveMode = false;

    /**
     * Generátor konfigurace NSC++
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        $this->host = $host;
        $preferences = new IEPreferences;
        $this->prefs = $preferences->getPrefs();
        $this->cfgInit();

        $this->hostActiveMode = (boolean) $host->getDataValue('active_checks_enabled');
        if ($this->hostActiveMode) {
            $this->cfgActiveSet();
        }
        $this->hostPassiveMode = (boolean) $host->getDataValue('passive_checks_enabled');
        if ($this->hostPassiveMode) {
            $this->cfgPassiveSet();
        }
        $this->cfgModules();
        $this->cfgServices();
        $this->cfgEnding();
    }

    /**
     * Připraví nouvou konfiguraci
     */
    function cfgInit()
    {
        $this->nscBatArray = array('
set NSCLIENT="%ProgramFiles%\NSClient++\nscp.exe"
%NSCLIENT% service --stop

del "%ProgramFiles%\NSClient++\nsclient.ini"
%NSCLIENT% settings --generate --add-defaults --load-all
');
    }

    /**
     * Nakonfiguruje režim pasivních testů odesílaných přez NSCA
     */
    function cfgActiveSet()
    {
        $this->nscBatArray[] = '
%NSCLIENT% settings --path "/modules" --key NRPEServer --set enabled
%NSCLIENT% settings --path "/settings/default" --key "allowed hosts" --set "' . $this->prefs['serverip'] . '"
';
    }

    /**
     * Nakonfiguruje režim pasivních testů odesílaných přez NSCA
     */
    function cfgPassiveSet()
    {
        $this->nscBatArray[] = '
%NSCLIENT% settings --path "/modules" --key Scheduler --set enabled
%NSCLIENT% settings --path "/modules" --key NSCAClient --set enabled
%NSCLIENT% settings --path /settings/NSCA/client --key hostname --set ' . $this->host->getName() . '
%NSCLIENT% settings --path /settings/NSCA/client --key channel --set NSCA
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key "allowed ciphers" --set "ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH"
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key encryption --set 3des
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key password --set "' . $this->prefs['nscapassword'] . '"
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key address --set ' . $this->prefs['serverip'] . '
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key port --set 5667
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key timeout --set 30
%NSCLIENT% settings --path /settings/scheduler/schedules/default --key interval --set 60s
';
    }

    /**
     * Povolí potřebné moduly testů
     */
    function cfgModules()
    {
        $this->nscBatArray[] = '
%NSCLIENT% settings --path "/modules" --key CheckDisk --set enabled
%NSCLIENT% settings --path "/modules" --key CheckEventLog --set enabled
%NSCLIENT% settings --path "/modules" --key CheckExternalScripts --set enabled
%NSCLIENT% settings --path "/modules" --key CheckHelpers --set enabled
%NSCLIENT% settings --path "/modules" --key CheckNSCP --set enabled
%NSCLIENT% settings --path "/modules" --key CheckSystem --set enabled
%NSCLIENT% settings --path "/modules" --key CheckWMI --set enabled
';
    }

    function cfgServices()
    {
        $service = new IEService();



        $servicesAssigned = $service->myDbLink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ',`use` FROM ' . $service->myTable . ' WHERE host_name LIKE \'%"' . $this->host->getName() . '"%\'', $service->myKeyColumn);


        $allServices = $service->getListing(
            null, true, array(
          'platform', 'check_command-remote', 'check_command-params', 'passive_checks_enabled', 'active_checks_enabled', 'use', 'check_interval', 'check_command-remote'
            )
        );

        foreach ($allServices as $serviceID => $serviceInfo) {
            if (!array_key_exists($serviceID, $servicesAssigned)) {
                unset($allServices[$serviceID]);
                continue; //Služba není přiřazena k hostu
            }
        }


        /* Naplní hodnoty z předloh */
        $usedCache = array();
        $commandsCache = array();
        foreach ($allServices as $rowId => $service) {
            if (isset($service['use'])) {
                $remote = $service['check_command-remote'];
                if (!isset($commandsCache[$remote])) {
                    $command = new IECommand($remote);
                    $commandsCache[$remote] = $command->getData();
                }
            }

            if (isset($service['use'])) {
                $use = $service['use'];

                if (!isset($usedCache[$use])) {
                    $used = new IEService;
                    $used->nameColumn = 'name';

                    if ($used->loadFromMySQL($use)) {
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

        foreach ($allServices as $serviceId => $service) {

            $serviceName = $service['service_description'];
            $serviceCmd = $service['check_command-remote'];
            $serviceParams = $service['check_command-params'];
            $this->nscBatArray[] = "\nREM #" . $service['service_id'] . ' ' . $serviceName . "\n";

            if (isset($commandsCache[$serviceCmd])) {
                $cmdline = $commandsCache[$serviceCmd]['command_line'];
            } else {
                $cmdline = $serviceCmd;
            }

            if (strstr($cmdline, 'scripts\\')) {
                $this->nscBatArray[] = '%NSCLIENT% settings --path "/settings/external scripts/wrapped scripts" --key "' . str_replace(' ', '_', $serviceName) . '" --set "' .
                    $cmdline . ' ' . $serviceParams . "\"\n";
            } else {
                $this->nscBatArray[] = '%NSCLIENT% settings --path "/settings/external scripts/alias" --key "' . str_replace(' ', '_', $serviceName) . '" --set "' . $cmdline . ' ' . $serviceParams . "\"\n";
            }

            if ($this->hostPassiveMode) {

                $this->nscBatArray[] = '%NSCLIENT% settings --path "/settings/scheduler/schedules/' . str_replace(' ', '_', $serviceName) . '-' . EaseShared::user()->getUserLogin() . '" --key command --set "' . str_replace(' ', '_', $serviceName) . "\"\n";

                $this->nscBatArray[] = '%NSCLIENT% settings --path "/settings/scheduler/schedules/' . str_replace(' ', '_', $serviceName) . '-' . EaseShared::user()->getUserLogin() . '" --key interval --set "' . $service['check_interval'] . "s\"\n";
            }
        }
    }

    public function cfgEnding()
    {
        $this->nscBatArray[] = '
%NSCLIENT% test
%NSCLIENT% service --start
';
    }

    public function getCfg()
    {
        $nscbat = implode('', $this->nscBatArray);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $this->host->getName() . '_nsca.bat');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($nscbat));
        echo str_replace("\n", "\r\n", $nscbat);
    }

}
