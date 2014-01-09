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
require_once 'classes/IEContact.php';
require_once 'classes/IEContactgroup.php';
require_once 'classes/IEHost.php';
require_once 'classes/IEHostgroup.php';
require_once 'classes/IETimeperiod.php';
require_once 'classes/IECommand.php';
require_once 'classes/IEServicegroup.php';


$oPage->onlyForLogged();


$oPage->addItem(new IEPageTop(_('Icinga Editor')));

$UserID = $oPage->getRequestValue('user_id','int');

$user= new EaseUser($UserID);

$UserInfoFrame = $oPage->columnI->addItem( new EaseHtmlFieldSet($user->getUserLogin()) );
$UserInfoFrame->addItem($user);



$contact = new IETimeperiod();
$PocTimeperiods = $contact->getMyRecordsCount($UserID);
if ($PocTimeperiods) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Timeperiod', new EaseTWBLinkButton('timeperiods.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s časových period'), $PocTimeperiods)), array('class' => 'alert alert-success')));
} 

$host = new IEHost();
$pocHostu = $host->getMyRecordsCount($UserID);
if ($pocHostu) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
} else {
    if ($PocTimeperiods) {
        $warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádný host'), array('class' => 'alert alert-info')));
        $warning->addItem(new EaseTWBLinkButton('host.php', _('Založit první host').' '.EaseTWBPart::GlyphIcon('edit')));
    }
}

$hostgroup = new IEHostgroup();
$PocHostgroups = $hostgroup->getMyRecordsCount($UserID);
if ($PocHostgroups) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Hostgroup', new EaseTWBLinkButton('hostgroups.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin hostů'), $PocHostgroups)), array('class' => 'alert alert-success')));
}


$Command = new IECommand();
$PocCommands = $Command->getMyRecordsCount($UserID);
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Command', new EaseTWBLinkButton('commands.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s příkazů'), $PocCommands)), array('class' => 'alert alert-success')));
}

$service = new IEService();
$PocServices = $service->getMyRecordsCount($UserID);
if ($PocServices) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Service', new EaseTWBLinkButton('services.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s služeb'), $PocServices)), array('class' => 'alert alert-success')));
} else {
    if ($PocCommands) {
        if ($PocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné služby'), array('class' => 'alert alert-info')));
            $warning->addItem(new EaseTWBLinkButton('service.php', _('Založit první službu') . ' <i class="icon-edit"></i>'));
        }
    }
}

$Servicegroup = new IEServicegroup();
$PocServicegroups = $Servicegroup->getMyRecordsCount($UserID);
if ($PocServicegroups) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Servicegroup', new EaseTWBLinkButton('servicegroups.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin služeb'), $PocServicegroups)), array('class' => 'alert alert-success')));
}

if($oUser->getSettingValue('admin')){
    $oPage->columnI->addItem(new EaseTWBLinkButton('login.php?force_id='.$UserID, _('Přihlásit se jako uživatel <i class="icon-refresh"></i>')));
}

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
