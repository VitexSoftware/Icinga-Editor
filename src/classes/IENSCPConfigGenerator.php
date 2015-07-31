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
     * Platforma
     * @var string
     */
    public $platform = 'generic';

    /**
     * Volání proměnné s nsclient
     * @var string
     */
    private $nscvar = '';

    /**
     * Pole skriptů pro deploy
     * @var array
     */
    private $scriptsToDeploy = array();

    /**
     * Generátor konfigurace NSC++
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        $this->host = $host;
        $this->setPlatform($host->getDataValue('platform'));

        $preferences = new IEPreferences;
        $this->prefs = $preferences->getPrefs();
        $this->cfgInit();
        $this->cfgGeneralSet();

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
     * Vloží do kongfiguráku další direktivu
     *
     * @param string $path
     * @param string $key
     * @param string $value
     */
    private function addCfg($path, $key, $value)
    {
        $this->nscBatArray[] = "\n" . $this->nscvar . ' settings --path "' . $path . '" --key "' . $key . '" --set "' . $value . '"';
    }

    /**
     * Nastaví platformu
     * @param type $platform
     */
    private function setPlatform($platform)
    {
        $this->platform = $platform;
        $this->setnscvar($platform);
    }

    /**
     * Nastaví Proměnnou pro volání nsclienta ve výsledném skriptu
     *
     * @param string $platform
     */
    private function setnscvar($platform)
    {
        switch ($platform) {
            case 'windows':
                $nsclient = '%NSCLIENT%';
                break;
            case 'linux':
                $nsclient = '$NSCLIENT';
                break;
            default:
                $nsclient = 'nsclient';
                break;
        }
        $this->nscvar = $nsclient;
    }

    /**
     * Připraví nouvou konfiguraci
     */
    function cfgInit()
    {
        switch ($this->platform) {
            case 'windows':
                $this->nscBatArray = array('
@ECHO OFF
set NSCDIR="%ProgramFiles%\NSClient++\"
set NSCLIENT="%NSCDIR%\nscp.exe"
set ICINGA_SERVER="' . $this->prefs['serverip'] . '"
' . $this->nscvar . ' service --stop
rm  "%ProgramFiles%\NSClient++\nsclient.old"
rename "%ProgramFiles%\NSClient++\nsclient.ini" nsclient.old
');

                $this->nscBatArray[] = "\n" . 'SET ICIEDIT_HTML="%NSCDIR%/icinga-editor.htm"';
                $this->nscBatArray[] = "\n" . 'echo "<html>" > "%ICIEDIT_HTML%"';
                $this->nscBatArray[] = "\n" . 'echo "<body>" >> "%ICIEDIT_HTML%"
';

                break;
            case 'linux':
                $this->nscBatArray = array('
export NSCLIENT=`which nscp`
export ICINGA_SERVER="' . $this->prefs['serverip'] . '"
' . $this->nscvar . ' service --stop
export INI="/etc/nsclient/nsclient.ini"
rm "$INI"

echo "[/paths]" >> $INI
echo "" >> $INI
echo "shared-path=/usr/share/nsclient/" >> $INI
echo "module-path=/usr/lib/nsclient/modules/" >> $INI
echo "log-path=/var/log/nsclient" >> $INI
echo "" >> $INI
echo "[/settings/log]" >> $INI
echo "file name=${log-path}/nsclient.log" >> $INI
');
                break;
        }
        $this->nscBatArray[] = $this->nscvar . ' settings --generate';
    }

    /**
     * Nakonfiguruje režim pasivních testů odesílaných přez NSCA
     */
    function cfgGeneralSet()
    {
        $this->addCfg('/settings/external scripts', 'timeout', '3600');

        switch ($this->platform) {
            case 'windows':
                $this->addCfg('/settings/external scripts/wrappings', 'vbs', 'cscript.exe //T:3600 //NoLogo scripts\\lib\\wrapper.vbs %%SCRIPT%% %%ARGS%%');
                break;
        }
    }

    /**
     * Nakonfiguruje režim pasivních testů odesílaných přez NSCA
     */
    function cfgActiveSet()
    {
        $this->addCfg('/modules', 'NRPEServer', 'enabled');
        $this->addCfg('/settings/default', 'allowed hosts', $this->prefs['serverip']);
    }

    /**
     * Nakonfiguruje režim pasivních testů odesílaných přez NSCA
     */
    function cfgPassiveSet()
    {
        $this->addCfg('/modules', 'Scheduler', 'enabled');
        $this->addCfg('/modules', 'NSCAClient', 'enabled');
        $this->addCfg('/settings/NSCA/client', 'hostname', $this->host->getName());
        $this->addCfg('/settings/NSCA/client', 'channel', 'NSCA');
        $this->addCfg('/settings/NSCA/client/targets/default', 'allowed ciphers', 'ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH');
        $this->addCfg('/settings/NSCA/client/targets/default', 'encryption', '3des');
        $this->addCfg('/settings/NSCA/client/targets/default', 'password', $this->prefs['nscapassword']);
        $this->addCfg('/settings/NSCA/client/targets/default', 'address', $this->prefs['serverip']);
        $this->addCfg('/settings/NSCA/client/targets/default', 'port', '5667');
        $this->addCfg('/settings/NSCA/client/targets/default', 'timeout', '30');
        $this->addCfg('/settings/NSCA/client/targets/default', 'interval', '60s');

        $host_check_interval = $this->host->getCfgValue('check_interval');
        if (is_null($host_check_interval)) {
            $host_check_interval = 5;
        }

        $this->addCfg('/settings/external scripts/alias', 'host_check', 'check_ok message=Host\ UP');
        $this->addCfg('/settings/scheduler/schedules/host_check', 'command', 'host_check');
        $this->addCfg('/settings/scheduler/schedules/host_check', 'interval', $host_check_interval . 'm');
    }

    /**
     * Povolí potřebné moduly testů
     */
    function cfgModules()
    {
        switch ($this->platform) {
            case 'windows':
                $this->addCfg('/modules', 'CheckWMI', 'enabled');
                $this->addCfg('/modules', 'CheckSystem', 'enabled');
                $this->addCfg('/modules', 'CheckDisk', 'enabled');
                if ($this->hostActiveMode) {
                    $this->addCfg('/modules', 'CheckEventLog', 'enabled');
                }
                break;
            case 'linux':
                $this->addCfg('/modules', 'CheckSystemUnix', 'enabled');
                break;
            default:
                break;
        }
        $this->addCfg('/modules', 'CheckHelpers', 'enabled');
        $this->addCfg('/modules', 'CheckNSCP', 'enabled');
        $this->addCfg('/modules', 'CheckExternalScripts', 'enabled');
        $this->addCfg('/settings/external scripts/server', 'allow arguments', 'enabled');
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
                    $script_id = $command->getDataValue('script_id');
                    if ($script_id) {
                        $this->scriptsToDeploy[$command->getName()] = $script_id;
                    }
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
            if (is_null($serviceCmd)) {
                continue;
            }
            $serviceParams = $service['check_command-params'];
            switch ($this->platform) {
                case 'windows':
                    $this->nscBatArray[] = "\n\nREM #" . $service['service_id'] . ' ' . $serviceName . "\n";
                    break;
                case 'linux':
                    $this->nscBatArray[] = "\n\n# #" . $service['service_id'] . ' ' . $serviceName . "\n";
                    break;
                default:
                    break;
            }

            if (isset($commandsCache[$serviceCmd])) {
                $cmdline = $commandsCache[$serviceCmd]['command_line'];
            } else {
                $cmdline = $serviceCmd;
            }

            if (preg_match("/\.(vbs|bat)/", $cmdline)) {
                $this->addCfg('/settings/external scripts/wrapped scripts', str_replace(' ', '_', $serviceName), $cmdline . ' ' . $serviceParams);
            } else {
                $this->addCfg('/settings/external scripts/alias', str_replace(' ', '_', $serviceName), $cmdline . ' ' . $serviceParams);
            }

            if ($this->hostPassiveMode) {
                $this->addCfg('/settings/scheduler/schedules/' . str_replace(' ', '_', $serviceName) . '-' . EaseShared::user()->getUserLogin(), 'command', str_replace(' ', '_', $serviceName));
                $this->addCfg('/settings/scheduler/schedules/' . str_replace(' ', '_', $serviceName) . '-' . EaseShared::user()->getUserLogin(), 'interval', $service['check_interval'] . 'm');
            }
        }
    }

    /**
     * Spustí testovací režim a po jeho ukončení nastartuje službu
     */
    public function cfgEnding()
    {
        if (count($this->scriptsToDeploy)) {
            $this->deployScripts();
        }
        switch ($this->platform) {
            case 'windows':
                $this->nscBatArray[] = "\n" . 'echo "<br><a href=' . IECfg::getBaseURL() . 'nscpcfggen.php?host_id=' . $this->host->getId() . '>' . _('Znovu stahnout') . '</a>" >> "%ICIEDIT_HTML%"';
                $this->nscBatArray[] = "\n" . 'echo "<br><a href=' . $this->getCfgConfirmUrl() . '>' . _('Potvrzení konfigurace') . '</a>" >> "%ICIEDIT_HTML%"';
                $this->nscBatArray[] = "\n" . 'echo "</body>" >> "%ICIEDIT_HTML%"';
                $this->nscBatArray[] = "\n" . 'echo "</html>" >> "%ICIEDIT_HTML%"
';

                $this->nscBatArray[] = "\n" . '
' . $this->nscvar . ' service --start
start "" "%ICIEDIT_HTML%"
';
                break;
            case 'linux':
                $this->nscBatArray[] = "\n" . '
curl "' . $this->getCfgConfirmUrl() . '"
service nscp start
';
                break;
            default:
                $this->nscBatArray[] = $this->nscBatArray[] = "\n" . '
';
                break;
        }
    }

    /**
     * vrací vyrendrovaný konfigurační skript
     *
     * @param boolean $send Přidat HTTP hlavičku pro odeslání souboru
     * @return string BAT soubor
     */
    public function getCfg($send = TRUE)
    {
        $nscbat = implode('', $this->nscBatArray);
        if ($send) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        switch ($this->platform) {
            case 'windows':
                if ($send) {
                    header('Content-Disposition: attachment; filename=' . $this->host->getName() . '_nscp.bat');
                }
                $nscbat = str_replace("\n", "\r\n", $nscbat);
                break;
            case 'linux':
                if ($send) {
                    header('Content-Disposition: attachment; filename=' . $this->host->getName() . '_nscp.sh');
                }
                break;
            default:
                break;
        }
        if ($send) {
            header('Content-Length: ' . strlen($nscbat));
            echo $nscbat;
        } else {
            return $nscbat;
        }
    }

    function getCfgConfirmUrl()
    {
        return IECfg::getBaseURL() . 'cfgconfirm.php?hash=' . $this->host->getConfigHash() . '&host_id=' . $this->host->getId();
    }

    /**
     * Nasazení externích skriptů
     */
    public function deployScripts()
    {

        foreach ($this->scriptsToDeploy as $script_name => $script_id) {
            switch ($this->platform) {
                case 'windows':
                    $this->nscBatArray[] = "\n" . 'echo "<br><a href=' . IECfg::getBaseURL() . 'scriptget.php?script_id=' . $script_id . '>' . $script_name . '</a>" >> "%ICIEDIT_HTML%"
';

                    break;
                case 'linux':
                    $this->nscBatArray[] = "\n" . '
# ' . $script_name . '
curl "' . IECfg::getBaseURL() . 'scriptget.php?script_id=' . $script_id . '"
';
                    break;
                default:
                    $this->nscBatArray[] = $this->nscBatArray[] = "\n" . '
' . $this->nscvar . ' test
';
                    break;
            }
        }
    }

}
