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


$Timeperiod = new IETimeperiod();
$PocTimeperiods = $Timeperiod->getMyRecordsCount();
if ($PocTimeperiods) {
    $Success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Timeperiod', new EaseTWBLinkButton('timeperiods.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s časových period'), $PocTimeperiods)), array('class' => 'alert alert-success')));
} else {
    $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné časové periody'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('timeperiod.php', _('Založit první časovou periodu <i class="icon-edit"></i>')));
}



$contact = new IEContact();
$PocContact = $contact->getMyRecordsCount();
if ($PocContact) {
    $Success = $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', new EaseTWBLinkButton('contacts.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s kontaktů'), $PocContact)), array('class' => 'alert alert-success')));
    
} else {
    if ($PocTimeperiods) {
        $Warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', _('Nemáte definovaný kontakt'), array('class' => 'alert alert-info')));
        $Warning->addItem(new EaseTWBLinkButton('contact.php', _('Založit první kontakt '.EaseTWBPart::GlyphIcon('edit'))));
    } else {
        $oPage->columnII->addItem(new EaseHtmlDivTag('Contact', _('Kontakty vyžadují časovou periodu'), array('class' => 'alert alert-error')));
    }
}

$Contactgroup = new IEContactgroup();
$PocContactgroup = $Contactgroup->getMyRecordsCount();
if ($PocContactgroup) {
    $Success = $oPage->columnII->addItem(new EaseHtmlDivTag('Contactgroup', new EaseTWBLinkButton('contactgroups.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin kontaktů'), $PocContactgroup)), array('class' => 'alert alert-success')));
    
} else {
    $Warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Contactgroup', _('Nemáte definovanou skupinu kontaktů'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('contactgroup.php', _('Založit první skupinu kontaktů '.EaseTWBPart::GlyphIcon('edit'))));
}


$host = new IEHost();
$PocHostu = $host->getMyRecordsCount();
if ($PocHostu) {
    $Success = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s hostů'), $PocHostu)), array('class' => 'alert alert-success')));
} else {
    if ($PocTimeperiods) {
        $Warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádný host'), array('class' => 'alert alert-info')));
        $Warning->addItem(new EaseTWBLinkButton('host.php', _('Založit první host').' '.EaseTWBPart::GlyphIcon('edit')));
    } else {
        $Warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Hosty vyžadují časovou periodu ..'), array('class' => 'alert alert-error')));
    }
}

$Hostgroup = new IEHostgroup();
$PocHostgroups = $Hostgroup->getMyRecordsCount();
if ($PocHostgroups) {
    $Success = $oPage->columnI->addItem(new EaseHtmlDivTag('Hostgroup', new EaseTWBLinkButton('hostgroups.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin hostů'), $PocHostgroups)), array('class' => 'alert alert-success')));
} else {
    $Warning = $oPage->columnI->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádnou skupinu hostů'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('hostgroup.php', _('Založit první skupinu hostů <i class="icon-edit"></i>')));
}


$Command = new IECommand();
$PocCommands = $Command->getMyRecordsCount();
if ($PocCommands) {
    $Success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Command', new EaseTWBLinkButton('commands.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s příkazů'), $PocCommands)), array('class' => 'alert alert-success')));
    
} else {
    $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné příkazy'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('importcommand.php', _('Importovat příkazy') . ' <i class="icon-download"></i>'));
    
}


$service = new IEService();
$PocServices = $service->getMyRecordsCount();
if ($PocServices) {
    $Success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Service', new EaseTWBLinkButton('services.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s služeb'), $PocServices)), array('class' => 'alert alert-success')));
} else {
    if ($PocCommands) {
        if ($PocTimeperiods) {
            $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné služby'), array('class' => 'alert alert-info')));
            $Warning->addItem(new EaseTWBLinkButton('service.php', _('Založit první službu') . ' <i class="icon-edit"></i>'));
        } else {
            $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Služby vyžadují časovou periodu ..'), array('class' => 'alert alert-error')));
        }
    } else {
        $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Služby vyžadují příkazy ..'), array('class' => 'alert alert-error')));
    }
}

$Servicegroup = new IEServicegroup();
$PocServicegroups = $Servicegroup->getMyRecordsCount();
if ($PocServicegroups) {
    $Success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Servicegroup', new EaseTWBLinkButton('servicegroups.php', _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin služeb'), $PocServicegroups)), array('class' => 'alert alert-success')));
} else {
    $Warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', _('Nemáte definovaný žádné skupiny služeb'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('servicegroup.php', _('Založit první skupinu služeb') . ' <i class="icon-edit"></i>'));
}

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
