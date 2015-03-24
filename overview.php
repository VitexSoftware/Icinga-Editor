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

$Timeperiod = new IETimeperiod();
$pocTimeperiods = $Timeperiod->getMyRecordsCount();
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Timeperiod', new EaseTWBLinkButton('timeperiods.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s časových period'), $pocTimeperiods)), array('class' => 'alert alert-success')));
} else {
    $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné časové periody'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('timeperiod.php', _('Založit první časovou periodu <i class="icon-edit"></i>')));
}

$contact = new IEContact();
$pocContact = $contact->getMyRecordsCount();
if ($pocContact) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', new EaseTWBLinkButton('contacts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s kontaktů'), $pocContact)), array('class' => 'alert alert-success')));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', _('Nemáte definovaný kontakt'), array('class' => 'alert alert-info')));
        $warning->addItem(new EaseTWBLinkButton('contact.php', _('Založit první kontakt ' . EaseTWBPart::GlyphIcon('edit'))));
    } else {
        $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', _('Kontakty vyžadují časovou periodu'), array('class' => 'alert alert-danger')));
    }
}

$Contactgroup = new IEContactgroup();
$pocContactgroup = $Contactgroup->getMyRecordsCount();
if ($pocContactgroup) {
    $success = $oPage->columnII->addItem(new EaseHtmlDivTag('Contactgroup', new EaseTWBLinkButton('contactgroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin kontaktů'), $pocContactgroup)), array('class' => 'alert alert-success')));
} else {
    $warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Contactgroup', _('Nemáte definovanou skupinu kontaktů'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('contactgroup.php', _('Založit první skupinu kontaktů ' . EaseTWBPart::GlyphIcon('edit'))));
}

$host = new IEHost();
$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádný host'), array('class' => 'alert alert-info')));
        $warning->addItem(new EaseTWBLinkButton('host.php', _('Založit první host') . ' ' . EaseTWBPart::GlyphIcon('edit')));
    } else {
        $warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Hosty vyžadují časovou periodu ..'), array('class' => 'alert alert-danger')));
    }
}

$hostgroup = new IEHostgroup();
$pocHostgroups = $hostgroup->getMyRecordsCount();
if ($pocHostgroups) {
    $success = $oPage->columnI->addItem(new EaseHtmlDivTag('Hostgroup', new EaseTWBLinkButton('hostgroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin hostů'), $pocHostgroups)), array('class' => 'alert alert-success')));
} else {
    $warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádnou skupinu hostů'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('hostgroup.php', _('Založit první skupinu hostů <i class="icon-edit"></i>')));
}

$command = new IECommand();
$PocCommands = $command->getMyRecordsCount();
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Command', new EaseTWBLinkButton('commands.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s příkazů'), $PocCommands)), array('class' => 'alert alert-success')));
} else {
    $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné příkazy'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('importcommand.php', _('Importovat příkazy') . ' <i class="icon-download"></i>'));
}

$service = new IEService();
$pocServices = $service->getMyRecordsCount();
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Service', new EaseTWBLinkButton('services.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s služeb'), $pocServices)), array('class' => 'alert alert-success')));
} else {
    if ($PocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné služby'), array('class' => 'alert alert-info')));
            $warning->addItem(new EaseTWBLinkButton('service.php', _('Založit první službu') . ' <i class="icon-edit"></i>'));
        } else {
            $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Služby vyžadují časovou periodu ..'), array('class' => 'alert alert-danger')));
        }
    } else {
        $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Služby vyžadují příkazy ..'), array('class' => 'alert alert-danger')));
    }
}

$serviceGroup = new IEServicegroup();
$pocServicegroups = $serviceGroup->getMyRecordsCount();
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Servicegroup', new EaseTWBLinkButton('servicegroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin služeb'), $pocServicegroups)), array('class' => 'alert alert-success')));
} else {
    $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné skupiny služeb'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('servicegroup.php', _('Založit první skupinu služeb') . ' <i class="icon-edit"></i>'));
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
