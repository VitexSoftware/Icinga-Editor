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

if($hostId == 0){
    $oPage->redirect('hosts.php');
    exit();
}

$host = new IEHost($hostId);

$oPage->addItem(new IEPageTop(_('Sensor')));


$oPage->columnI->addItem(new EaseHtmlH1Tag(_('NRPE Senzor')));

$oPage->columnI->addItem( new EaseHtmlDivTag(null,'sudo aptitude -y install nagios-nrpe-server' ) );
$oPage->columnI->addItem( new EaseHtmlDivTag(null,'sudo echo "allowed_hosts='.ICINGA_SERVER_IP.'" >> /etc/nagios/nrpe_local.cfg' ) );
$oPage->columnI->addItem( new EaseHtmlDivTag(null,'sudo echo "dont_blame_nrpe=1" >> /etc/nagios/nrpe_local.cfg' ) );
$oPage->columnI->addItem( new EaseHtmlDivTag(null,'sudo service nagios-nrpe-server reload' ) );


$oPage->columnII->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick'=>"$('#preload').css('visibility', 'visible');") ));
$oPage->addItem( new EaseHtmlDivTag('preload', new IEFXPreloader(),array('class'=>'fuelux')));



$oPage->addItem(new IEPageBottom());

$oPage->draw();
