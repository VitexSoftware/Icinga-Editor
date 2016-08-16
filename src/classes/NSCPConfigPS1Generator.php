<?php

namespace Icinga\Editor;

/**
 * Config Generator for PowerShell
 *
 * @author vitex
 */
class NSCPConfigPS1Generator extends NSCPConfigBatGenerator
{
    /**
     * Files in this format Suffix
     * @var type
     */
    public $formatSuffix = 'ps1';

    /**
     * Comment mark
     * @var string
     */
    public $comment = '#';

    /**
     * Nastaví Proměnnou pro volání nsclienta ve výsledném skriptu
     *
     * @param string $platform
     */
    public function setnscvar($platform)
    {
        switch ($platform) {
            case 'windows':
            case 'linux':
                $nsclient = '& $NSCLIENT';
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
                $this->nscConfArray = ['
$NSCDIR = ${Env:ProgramFiles} + "\\NSCLient++"
$NSCLIENT = $NSCDIR + "\\nscp.exe"
$NSCSCRIPTSDIR = $NSCDIR + "\\Scripts"
$ICINGA_SERVER = "'.$this->prefs['serverip'].'"
'.$this->nscvar.' service --stop
If (Test-Path "$NSCDIR\\nsclient.old"){ Remove-Item "$NSCDIR\\nsclient.old" }
If (Test-Path "$NSCDIR\\nsclient.ini"){ Rename-Item "$NSCDIR\\nsclient.ini" "$NSCDIR\\nsclient.old" }
'];

                $this->nscConfArray[] = "\n".'$ICIEDIT_HTML= $NSCDIR + "\\icinga-editor.htm"';
                $this->nscConfArray[] = "\n".'echo "<html>"  | Out-File $ICIEDIT_HTML';
                $this->nscConfArray[] = "\n".'echo "<head><meta charset="UTF-8"></head>"  | Out-File $ICIEDIT_HTML -Append';
                $this->nscConfArray[] = "\n".'echo "<body>"  | Out-File $ICIEDIT_HTML -Append
';

                break;
            case 'linux':
                $this->nscConfArray = ['
export NSCLIENT=`which nscp`
export ICINGA_SERVER="'.$this->prefs['serverip'].'"
'.$this->nscvar.' service --stop
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
'];
                break;
        }
        $this->nscConfArray[] = $this->nscvar.' settings --generate';
    }

    /**
     * Make HTML and start service
     */
    public function cfgEnding()
    {
        if (count($this->scriptsToDeploy)) {
            $this->deployScripts();
        }
        switch ($this->platform) {
            case 'windows':
                $this->nscConfArray[] = "\n".'echo "<h1>'._('Konfigurace hosta').' '.$this->host->getName().'</h1>"  | Out-File $ICIEDIT_HTML -Append';
                $this->nscConfArray[] = "\n".'echo "<br><a data-role="editor" href="'.Engine\Configurator::getBaseURL().'host.php?host_id='.$this->host->getId().'">'._('Host Configuration').'</a>"  | Out-File $ICIEDIT_HTML -Append';
                $this->nscConfArray[] = "\n".'echo "<br><a data-role="ps1" href="'.Engine\Configurator::getBaseURL().'nscpcfggen.php?host_id='.$this->host->getId().'&format=ps1">'._('Refresh sensor installation').' '.$this->host->getName().'_nscp.ps1'.'</a>"  | Out-File $ICIEDIT_HTML -Append';
                if ($this->host->getDataValue('host_is_server') == 0) {
                    $dtUrl                = Engine\Configurator::getBaseURL().'downtime.php?host_id='.$this->host->getId();
                    $this->nscConfArray[] = "\n"."echo '<br><a data-role=\"shutdown\" href=\"$dtUrl\"&\"state=start\">"._('Start host downtime')."</a>' | Out-File \$ICIEDIT_HTML -Append";
                    $this->nscConfArray[] = "\n"."echo '<br><a data-role=\"poweron\"  href=\"$dtUrl\"&\"state=stop\">"._('End host downtime')."</a>' | Out-File \$ICIEDIT_HTML -Append";
                }
                $this->nscConfArray[] = "\n"."echo '<br><a data-role=\"confirm\" href=\"".$this->getCfgConfirmUrl()."\">"._('Potvrzení konfigurace')."</a>'  | Out-File \$ICIEDIT_HTML -Append";
                $this->nscConfArray[] = "\n".'echo "</body>"  | Out-File $ICIEDIT_HTML -Append';
                $this->nscConfArray[] = "\n".'echo "</html>"  | Out-File $ICIEDIT_HTML -Append
';
                if ($this->host->getDataValue('host_is_server') == 0) {
                    $this->nscConfArray[] = "\n".$this->registryUpdaterCode();

                    $upfile               = 'C:\\Windows\\System32\\GroupPolicy\\Machine\\Scripts\\Startup\\hostup.ps1';
                    $this->nscConfArray[] = "\n"."echo '(New-Object System.Net.WebClient).DownloadFile(\"$dtUrl\"&\"state=stop\",\"C:\\WINDOWS\\TEMP\\UP.TXT\")' | Out-File $upfile";

                    $downfile             = 'C:\\Windows\\System32\\GroupPolicy\\Machine\\Scripts\\Shutdown\\hostdown.ps1';
                    $this->nscConfArray[] = "\n"."echo '(New-Object System.Net.WebClient).DownloadFile(\"$dtUrl\"&\"state=start\",\"C:\\WINDOWS\\TEMP\\DOWN.TXT\")' | Out-File $downfile";
                }

                $this->nscConfArray[] = "\n".'
'.$this->nscvar.' service --start
(New-Object System.Net.WebClient).DownloadFile("'.parent::getCfgConfirmUrl().'", $NSCDIR + "\\CONFIRM.HTM")
';

                $this->nscConfArray[] = "\n".
                    "echo  '(New-Object System.Net.WebClient).DownloadFile(\"".Engine\Configurator::getBaseURL().'nscpcfggen.php?host_id='.$this->host->getId().'&format=ps1&user='.\Ease\Shared::user()->getUserLogin().'","'.$this->host->getName().'_nscp.ps1")\' | Out-File $NSCDIR + \"\\refresh.ps1"';
                $this->nscConfArray[] = "\n".
                    "echo  '& ".$this->host->getName().'_nscp.ps1 | Out-File $NSCDIR + \"\\refresh.ps1" -Append';

                break;
            case 'linux':
                $this->nscConfArray[] = "\n".'
curl "'.$this->getCfgConfirmUrl().'"
service nscp start
';
                break;
            default:
                $this->nscConfArray[] = $this->nscConfArray[] = "\n".'
';
                break;
        }
    }

    /**
     * Nasazení externích skriptů
     */
    public function deployScripts()
    {
        if (count($this->scriptsToDeploy)) {
            switch ($this->platform) {
                case 'windows':
                    $this->nscConfArray[] = "\n".'echo "<h2>'._('Skripty').'</h2>"  | Out-File $ICIEDIT_HTML -Append
';
                    break;
            }


            foreach ($this->scriptsToDeploy as $script_name => $script_id) {
                switch ($this->platform) {
                    case 'windows':
                        $this->nscConfArray[] = "\n"."echo '<a data-role=\"script\" href=\"".Engine\Configurator::getBaseURL()."scriptget.php?script_id=$script_id\">$script_name</a><br>'  | Out-File \$ICIEDIT_HTML -Append\n";
                        $this->nscConfArray[] = "\n"."(New-Object System.Net.WebClient).DownloadFile(\"".Engine\Configurator::getBaseURL()."scriptget.php?script_id=$script_id\", \$NSCSCRIPTSDIR + \"\\\" + \"".$this->scriptsToDeployNames[$script_id]."\")\n";
                        break;
                    case 'linux':
                        $this->nscConfArray[] = "\n".'
# '.$script_name.'
curl "'.Engine\Configurator::getBaseURL().'scriptget.php?script_id='.$script_id.'"
';
                        break;
                    default:
                        $this->nscConfArray[] = $this->nscConfArray[] = "\n".'
'.$this->nscvar.' test
';
                        break;
                }
            }
        }
    }

    /**
     * Where To confirm sensor status ?
     *
     * @return string
     */
    function getCfgConfirmUrl()
    {
        return str_replace('&', '"&"', parent::getCfgConfirmUrl());
    }

public static function registryUpdaterCode()
    {
        return '$reg = "0"
$PathGP = "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Group Policy\"

$ScriptPathShutdown = "$PathGP"+"Scripts\Shutdown\0\"
$PathScriptShutdown = "$ScriptPathShutdown"+"$reg"

$ScriptPathStartup = "$PathGP"+"Scripts\Startup\0\"
$PathScriptStartup = "$ScriptPathStartup"+"$reg"

$ScriptShutdown = "hostdown.ps1"
$ScriptStartup = "hostup.ps1"

IF {
$PatternShutdown = Get-ItemPropertyValue -Path "$PathScriptShutdown" -Name "Script"
IF {
New-Item -Path $ScriptPathShutdown -Name $reg -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "Script" -Value $ScriptShutdown -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "Parameters" -Value "" -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "IsPowershell" -Value "1" -Force -PropertyType DWORD
New-ItemProperty -Path "$PathScriptShutdown" -Name "ExecTime" -Value "0" -Force -PropertyType QWORD

New-ItemProperty -Path $ScriptPathShutdown -Name "DisplayName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathShutdown -Name "FileSysPath" -Value "C:\WINDOWS\System32\GroupPolicy\Machine"
New-ItemProperty -Path $ScriptPathShutdown -Name "GPO-ID" -Value "LocalGPO"
New-ItemProperty -Path $ScriptPathShutdown -Name "GPOName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathShutdown -Name "PSScriptOrder" -Value "1" -PropertyType DWORD
New-ItemProperty -Path $ScriptPathShutdown -Name "SOM-ID" -Value "Local"
}
}
ELSE {
New-Item -Path $ScriptPathShutdown -Name $reg -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "Script" -Value $ScriptShutdown -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "Parameters" -Value "" -Force
New-ItemProperty -Path "$PathScriptShutdown" -Name "IsPowershell" -Value "1" -Force -PropertyType DWORD
New-ItemProperty -Path "$PathScriptShutdown" -Name "ExecTime" -Value "0" -Force -PropertyType QWORD

New-ItemProperty -Path $ScriptPathShutdown -Name "DisplayName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathShutdown -Name "FileSysPath" -Value "C:\WINDOWS\System32\GroupPolicy\Machine"
New-ItemProperty -Path $ScriptPathShutdown -Name "GPO-ID" -Value "LocalGPO"
New-ItemProperty -Path $ScriptPathShutdown -Name "GPOName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathShutdown -Name "PSScriptOrder" -Value "1" -PropertyType DWORD
New-ItemProperty -Path $ScriptPathShutdown -Name "SOM-ID" -Value "Local"
}

IF {
$PatternStartup = Get-ItemPropertyValue -Path "$PathScriptStartup" -Name "Script"
IF {
New-Item -Path $ScriptPathStartup -Name $reg -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "Script" -Value $ScriptStartup -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "Parameters" -Value "" -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "IsPowershell" -Value "1" -Force -PropertyType DWORD
New-ItemProperty -Path "$PathScriptStartup" -Name "ExecTime" -Value "0" -Force -PropertyType QWORD

New-ItemProperty -Path $ScriptPathStartup -Name "DisplayName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathStartup -Name "FileSysPath" -Value "C:\WINDOWS\System32\GroupPolicy\Machine"
New-ItemProperty -Path $ScriptPathStartup -Name "GPO-ID" -Value "LocalGPO"
New-ItemProperty -Path $ScriptPathStartup -Name "GPOName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathStartup -Name "PSScriptOrder" -Value "1" -PropertyType DWORD
New-ItemProperty -Path $ScriptPathStartup -Name "SOM-ID" -Value "Local"
}
}
ELSE {
New-Item -Path $ScriptPathStartup -Name $reg -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "Script" -Value $ScriptStartup -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "Parameters" -Value "" -Force
New-ItemProperty -Path "$PathScriptStartup" -Name "IsPowershell" -Value "1" -Force -PropertyType DWORD
New-ItemProperty -Path "$PathScriptStartup" -Name "ExecTime" -Value "0" -Force -PropertyType QWORD

New-ItemProperty -Path $ScriptPathStartup -Name "DisplayName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathStartup -Name "FileSysPath" -Value "C:\WINDOWS\System32\GroupPolicy\Machine"
New-ItemProperty -Path $ScriptPathStartup -Name "GPO-ID" -Value "LocalGPO"
New-ItemProperty -Path $ScriptPathStartup -Name "GPOName" -Value "Místní zásady skupiny"
New-ItemProperty -Path $ScriptPathStartup -Name "PSScriptOrder" -Value "1" -PropertyType DWORD
New-ItemProperty -Path $ScriptPathStartup -Name "SOM-ID" -Value "Local"
}
            ';
    }

}
