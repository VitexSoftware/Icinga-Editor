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
$oPage->addPageColumns();

$userID = $oPage->getRequestValue('user_id', 'int');
if ($userID) {
    $oPage->onlyForAdmin();
}

$user = new IEUser($userID);

if ($oPage->getRequestValue('delete') == 'true') {
    if ($user->delete()) {
        $oPage->redirect('users.php');
        exit();
    }
}

$userInfoFrame = $oPage->columnI->addItem(new EaseTWBPanel($user->getUserLogin()));
$userInfoFrame->addItem($user);
$userInfoFrame->addItem(new EaseHtmlUlTag(array($user->getUserName(), new EaseHtmlATag('mailto:' . $user->getEmail(), $user->getEmail()))));


$contact = new IETimeperiod();
$pocTimeperiods = $contact->getMyRecordsCount($userID);
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Timeperiod', new EaseTWBLinkButton('timeperiods.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s časových period'), $pocTimeperiods)), array('class' => 'alert alert-success')));
}

$host = new IEHost();
$pocHostu = $host->getMyRecordsCount($userID);
if ($pocHostu) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádný host'), array('class' => 'alert alert-info')));
        $warning->addItem(new EaseTWBLinkButton('host.php', _('Založit první host') . ' ' . EaseTWBPart::GlyphIcon('edit')));
    }
}

$hostgroup = new IEHostgroup();
$pocHostgroups = $hostgroup->getMyRecordsCount($userID);
if ($pocHostgroups) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Hostgroup', new EaseTWBLinkButton('hostgroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin hostů'), $pocHostgroups)), array('class' => 'alert alert-success')));
}

$command = new IECommand();
$PocCommands = $command->getMyRecordsCount($userID);
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Command', new EaseTWBLinkButton('commands.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s příkazů'), $PocCommands)), array('class' => 'alert alert-success')));
}

$service = new IEService();
$pocServices = $service->getMyRecordsCount($userID);
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Service', new EaseTWBLinkButton('services.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s služeb'), $pocServices)), array('class' => 'alert alert-success')));
} else {
    if ($PocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné služby'), array('class' => 'alert alert-info')));
            $warning->addItem(new EaseTWBLinkButton('service.php', _('Založit první službu') . ' <i class="icon-edit"></i>'));
        }
    }
}

$serviceGroup = new IEServicegroup();
$pocServicegroups = $serviceGroup->getMyRecordsCount($userID);
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Servicegroup', new EaseTWBLinkButton('servicegroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin služeb'), $pocServicegroups)), array('class' => 'alert alert-success')));
}

if ($oUser->getSettingValue('admin')) {
    $oPage->columnI->addItem(new EaseTWBLinkButton('login.php?force_id=' . $userID, _('Přihlásit se jako uživatel <i class="icon-refresh"></i>')));
}

$oPage->columnIII->addItem($user->deleteButton());

$oPage->addItem(new IEPageBottom());

$oPage->draw();
