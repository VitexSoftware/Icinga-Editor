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
require_once 'classes/IEHost.php';
require_once 'classes/IEPreferences.php';

$hostId = $oPage->getRequestValue('host_id', 'int');
$host = new IEHost($hostId);

$preferences = new IEPreferences;
$prefs = $preferences->getPrefs();

$nscabat = '@echo OFF
REM http://www.nsclient.org/download/
set cver=0.4.3.88
set server_ip=' . $prefs['serverip'] . '
set inst_params=GENERATE_SAMPLE_CONFIG=0 ALLOW_CONFIGURATION=1 INSTALL_SAMPLE_CONFIG=0 ALLOWED_HOSTS=127.0.0.1,::1,%server_ip% TARGETDIR="%ProgramFiles%"

SET mypath=%~dp0
SET script_path=%mypath:~0,-1%

set NSCLIENT="%ProgramFiles%\NSClient++\nscp.exe"


if /i "%processor_architecture%"=="x86" (
    if exist %NSCLIENT% (
        echo ***App is Installed Successfully***
    ) else (
        echo ***INSTALLING NSCP-%cver%-Win32.msi ***
	msiexec.exe /qb /lv %script_path%\instlog.txt  /a %script_path%\NSCP-%cver%-Win32.msi %inst_params%
    )
) else if /i "%processor_architecture%"=="X64" (
    if exist %NSCLIENT% (
        echo ***App is Installed Successfully***
    ) else (
        echo *** INSTALLING NSCP-%cver%-x64.msi ***
	msiexec.exe /qb /a %script_path%\NSCP-%cver%-x64.msi  %inst_params%
    )
)

if exist "%ProgramFiles%\NSCP*.msi" del "%ProgramFiles%\NSCP*.msi"

xcopy "%ProgramFiles%\NSClient++\Win\System\*" "%SystemRoot%\System32" /s/e/c/k/i/y

%NSCLIENT% service --install
%NSCLIENT% service --stop

%NSCLIENT% settings --generate --add-defaults --load-all
%NSCLIENT% settings --path /modules --key Scheduler --set enabled
%NSCLIENT% settings --path /modules --key CheckDisk --set enabled
%NSCLIENT% settings --path /modules --key CheckEventLog --set enabled
%NSCLIENT% settings --path /modules --key CheckExternalScripts --set enabled
%NSCLIENT% settings --path /modules --key CheckHelpers --set enabled
%NSCLIENT% settings --path /modules --key CheckNSCP --set enabled
%NSCLIENT% settings --path /modules --key CheckSystem --set enabled
%NSCLIENT% settings --path /modules --key CheckWMI --set enabled
%NSCLIENT% settings --path /modules --key NRPEServer --set enabled
%NSCLIENT% settings --path /modules --key NSCAClient --set enabled
%NSCLIENT% settings --path /settings/NSCA/client --key hostname --set ' . $host->getName() . '
%NSCLIENT% settings --path /settings/NSCA/client --key channel --set NSCA
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key "allowed ciphers" --set "ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH"
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key encryption --set 3des
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key password --set ' . $prefs['nscapassword'] . '
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key address --set %server_ip%
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key port --set 5667
%NSCLIENT% settings --path /settings/NSCA/client/targets/default --key timeout --set 30

%NSCLIENT% settings --path "/settings/external scripts/alias" --key alias_cpu --set checkCPU warn=80 crit=90 time=5m time=1m time=30s

%NSCLIENT% settings --path /settings/scheduler/schedules/default --key channel --set NSCA
%NSCLIENT% settings --path /settings/scheduler/schedules/default --key interval --set 30s
%NSCLIENT% settings --path /settings/scheduler/schedules/default --key report --set all
%NSCLIENT% settings --path /settings/scheduler/schedules --key "CPU Load-vitex" --set alias_cpu

REM %NSCLIENT% test
%NSCLIENT% service --start

    ';




if ($host->getDataValue('passive_checks_enabled')) {
    if ($host->getDataValue('platform') == 'windows') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $host->getName() . '_nsca.bat');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($nscabat));
        echo str_replace("\n", "\r\n", $nscabat);
    }
}