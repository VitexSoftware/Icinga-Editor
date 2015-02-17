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

$oPage->addItem(new IEPageTop(_('Sensor')));

switch ($host->getDataValue('platform')) {
    case 'windows':
        $pltIco = 'logos/base/win40.gif';
        $oPage->columnIII->addItem(new EaseTWBLinkButton('http://www.nsclient.org/download/', ' NSC++ ' . EaseTWBPart::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));

        if ($host->getDataValue('active_checks_enabled')) {
            $oPage->columnI->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NSC++')));
            $oPage->columnII->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
        }
        if ($host->getDataValue('passive_checks_enabled')) {
            $oPage->columnI->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSC++')));
        }
        $oPage->columnI->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $hostId, $host->getName() . '_nrpe.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
        break;
    case 'linux':
        $pltIco = 'logos/base/linux40.gif';
        $oPage->columnIII->addItem(new EaseHtmlDivTag(null, 'sudo aptitude -y install nagios-nrpe-server'));
        $oPage->columnIII->addItem(new EaseHtmlDivTag(null, 'sudo echo "allowed_hosts=' . ICINGA_SERVER_IP . '" >> /etc/nagios/nrpe_local.cfg'));
        $oPage->columnIII->addItem(new EaseHtmlDivTag(null, 'sudo echo "dont_blame_nrpe=1" >> /etc/nagios/nrpe_local.cfg'));
        $oPage->columnIII->addItem(new EaseHtmlDivTag(null, 'sudo service nagios-nrpe-server reload'));

        if ($host->getDataValue('active_checks_enabled')) {
            $oPage->columnI->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NRPE Server')));

            $oPage->columnII->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
        }
        if ($host->getDataValue('passive_checks_enabled')) {
            $oPage->columnI->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasiví NSCA pro NRPE Server')));
        }
        break;
    default:
        $pltIco = 'logos/unknown.gif';
        if ($host->getDataValue('active_checks_enabled')) {
            $oPage->columnII->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
        }
        if ($host->getDataValue('passive_checks_enabled')) {

        }
        break;
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
