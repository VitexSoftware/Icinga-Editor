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

if ($host->getDataValue('active_checks_enabled')) {
    $oPage->columnI->addItem(new EaseHtmlH1Tag(_('NRPE Senzor')));

    if ($host->getDataValue('platform') == 'linux') {

        $oPage->columnI->addItem(new EaseHtmlDivTag(null, 'sudo aptitude -y install nagios-nrpe-server'));
        $oPage->columnI->addItem(new EaseHtmlDivTag(null, 'sudo echo "allowed_hosts=' . ICINGA_SERVER_IP . '" >> /etc/nagios/nrpe_local.cfg'));
        $oPage->columnI->addItem(new EaseHtmlDivTag(null, 'sudo echo "dont_blame_nrpe=1" >> /etc/nagios/nrpe_local.cfg'));
        $oPage->columnI->addItem(new EaseHtmlDivTag(null, 'sudo service nagios-nrpe-server reload'));

        $oPage->columnII->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
    }

    if ($host->getDataValue('platform') == 'windows') {
        $oPage->columnI->addItem(new EaseTWBLinkButton('nrpe.php?host_id=' . $hostId, $host->getName() . '_nrpe.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
    }
}

if ($host->getDataValue('passive_checks_enabled')) {
    $oPage->columnI->addItem(new EaseHtmlH1Tag(_('NSCA Senzor')));
    if ($host->getDataValue('platform') == 'windows') {
        $oPage->columnI->addItem(new EaseTWBLinkButton('nsca.php?host_id=' . $hostId, $host->getName() . '_nsca.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
    }

    if ($host->getDataValue('platform') == 'linux') {
        $oPage->columnI->addItem('Passive Linux');
    }
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
