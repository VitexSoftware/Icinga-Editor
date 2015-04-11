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
require_once 'classes/IEFXPreloader.php';

$oPage->onlyForLogged();

$hostId = $oPage->getRequestValue('host_id', 'int');

if ($hostId == 0) {
    $oPage->redirect('hosts.php');
    exit();
}

$host = new IEHost($hostId);

$operation = $oPage->getRequestValue('operation');
switch ($operation) {
    case 'confirm':
        $state = $oPage->getRequestValue('confirm');
        if ($state == 'on') {
            $host->setDataValue('config_hash', $host->getConfigHash());
        } else {
            $host->setDataValue('config_hash', null);
        }
        if ($host->saveToMySQL()) {
            $host->addStatusMessage(_('Stav nasazení senzoru byl nastaven  ručně.'));
        }

        break;

    default:
        break;
}


$oPage->addItem(new IEPageTop(_('Sensor')));

$commonWell = new EaseTWBWell();

$commonRow = new EaseTWBRow;
$hostColumn = $commonRow->addColumn(4, new EaseHtmlH1Tag($host->getName()));
$hostColumn->addItem($host);
$hostColumn->addItem($host->sensorStatusLabel());

$commonWell->addItem($commonRow);

$commonRow->addColumn(4, new EaseTWBPanel(_('Ruční nastavení stavu senzoru'), 'info', new IESensorConfirmForm($host)));

$oPage->container->addItem($commonWell);


$sensorTabs = new EaseTWBTabs('sensorTabs');



switch ($host->getDataValue('platform')) {
    case 'windows':
        $pltIco = 'logos/base/win40.gif';
        $cfgGenerator = new IENSCPConfigGenerator($host);

        if ($host->getCfgValue('active_checks_enabled')) {
            $windowsActiveTab = $sensorTabs->addTab(_('Windows NRPE'));
            $windowsActiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NSC++')));
            $windowsActiveTab->addItem(new EaseTWBLinkButton('http://www.nsclient.org/download/', ' NSC++ ' . EaseTWBPart::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
            $windowsActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
            $windowsActiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $hostId, $host->getName() . '_nscp.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
            $windowsActiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
        }
        if ($host->getCfgValue('passive_checks_enabled')) {
            $windowsPassiveTab = $sensorTabs->addTab(_('Windows NSCA'));
            $windowsPassiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSC++')));
            $windowsPassiveTab->addItem(new EaseTWBLinkButton('http://www.nsclient.org/download/', ' NSC++ ' . EaseTWBPart::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
            $windowsPassiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $hostId, $host->getName() . '_nscp.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
            $windowsPassiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
        }

        break;
    case 'linux':
        $pltIco = 'logos/base/linux40.gif';

        $preferences = new IEPreferences;
        $prefs = $preferences->getPrefs();

        if ($host->getCfgValue('active_checks_enabled')) {
            $linuxActiveTab = $sensorTabs->addTab(_('Linux NRPE'));
            $linuxActiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NRPE Server')));
            $linuxActiveTab->addItem(new EaseHtmlDivTag(null, 'sudo aptitude -y install nagios-nrpe-server'));
            $linuxActiveTab->addItem(new EaseHtmlDivTag(null, 'sudo echo "allowed_hosts=' . $prefs['serverip'] . '" >> /etc/nagios/nrpe_local.cfg'));
            $linuxActiveTab->addItem(new EaseHtmlDivTag(null, 'sudo echo "dont_blame_nrpe=1" >> /etc/nagios/nrpe_local.cfg'));
            $linuxActiveTab->addItem(new EaseHtmlDivTag(null, 'sudo service nagios-nrpe-server reload'));

            $linuxActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
        }
        if ($host->getCfgValue('passive_checks_enabled')) {
            $linuxPassiveTab = $sensorTabs->addTab(_('Linux NSCA'));
            $linuxPassiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSCP Senzor')));
            $linuxPassiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $hostId, $host->getName() . '_nscp.sh ' . EaseTWBPart::GlyphIcon('download'), 'success'));

            $cfgGenerator = new IENSCPConfigGenerator($host);
            $linuxPassiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
        }
        break;
    default:
        $pltIco = 'logos/unknown.gif';
        if ($host->getCfgValue('active_checks_enabled')) {
            $genericActiveTab = $sensorTabs->addTab(_('Generic Active'));
            $genericActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
        }
        if ($host->getCfgValue('passive_checks_enabled')) {
            $genericPassiveTab = $sensorTabs->addTab(_('Generic Passive'));
        }
        break;
}




$oPage->container->addItem($sensorTabs);


$oPage->addItem(new IEPageBottom());

$oPage->draw();
