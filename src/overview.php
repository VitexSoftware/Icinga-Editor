<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$Timeperiod     = new Engine\IETimeperiod();
$pocTimeperiods = $Timeperiod->getMyRecordsCount();
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Timeperiod',
        new \Ease\TWB\LinkButton('timeperiods.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s časových period'),
            $pocTimeperiods)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
        _('Nemáte definovaný žádné časové periody'),
        ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('timeperiod.php',
        _('Založit první časovou periodu <i class="icon-edit"></i>')));
}

$contact    = new Engine\IEContact();
$pocContact = $contact->getMyRecordsCount();
if ($pocContact) {
    $success = $oPage->columnII->addItem(new \Ease\Html\DivTag('Contact',
        new \Ease\TWB\LinkButton('contacts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s kontaktů'),
            $pocContact)), ['class' => 'alert alert-success']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new \Ease\Html\DivTag('Contact',
            _('Nemáte definovaný kontakt'), ['class' => 'alert alert-info']));
        $warning->addItem(new \Ease\TWB\LinkButton('contact.php',
            _('Založit první kontakt '.\Ease\TWB\Part::GlyphIcon('edit'))));
    } else {
        $oPage->columnII->addItem(new \Ease\Html\DivTag('Contact',
            _('Kontakty vyžadují časovou periodu'),
            ['class' => 'alert alert-danger']));
    }
}

$contactgroup    = new Engine\IEContactgroup();
$pocContactgroup = $contactgroup->getMyRecordsCount();
if ($pocContactgroup) {
    $success = $oPage->columnII->addItem(new \Ease\Html\DivTag('Contactgroup',
        new \Ease\TWB\LinkButton('contactgroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin kontaktů'),
            $pocContactgroup)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnII->addItem(new \Ease\Html\DivTag('Contactgroup',
        _('Nemáte definovanou skupinu kontaktů'),
        ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('contactgroup.php',
        _('Založit první skupinu kontaktů '.\Ease\TWB\Part::GlyphIcon('edit'))));
}

$host     = new Engine\IEHost();
$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnI->addItem(new \Ease\Html\DivTag('Host',
        new \Ease\TWB\LinkButton('hosts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s hostů'),
            $pocHostu)), ['class' => 'alert alert-success']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnI->addItem(new \Ease\Html\DivTag('Host',
            _('Nemáte definovaný žádný host'), ['class' => 'alert alert-info']));
        $warning->addItem(new \Ease\TWB\LinkButton('host.php',
            _('Založit první host').' '.\Ease\TWB\Part::GlyphIcon('edit')));
    } else {
        $warning = $oPage->columnI->addItem(new \Ease\Html\DivTag('Host',
            _('Hosty vyžadují časovou periodu ..'),
            ['class' => 'alert alert-danger']));
    }
}

$hostgroup     = new Engine\IEHostgroup();
$pocHostgroups = $hostgroup->getMyRecordsCount();
if ($pocHostgroups) {
    $success = $oPage->columnI->addItem(new \Ease\Html\DivTag('Hostgroup',
        new \Ease\TWB\LinkButton('hostgroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin hostů'),
            $pocHostgroups)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnI->addItem(new \Ease\Html\DivTag('Host',
        _('Nemáte definovaný žádnou skupinu hostů'),
        ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('hostgroup.php',
        _('Založit první skupinu hostů <i class="icon-edit"></i>')));
}

$command     = new Engine\IECommand();
$PocCommands = $command->getMyRecordsCount();
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Command',
        new \Ease\TWB\LinkButton('commands.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s příkazů'),
            $PocCommands)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
        _('Nemáte definovaný žádné příkazy'), ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('importcommand.php',
        _('Importovat příkazy').' <i class="icon-download"></i>'));
}

$service     = new Engine\IEService();
$pocServices = $service->getMyRecordsCount();
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Service',
        new \Ease\TWB\LinkButton('services.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s služeb'),
            $pocServices)), ['class' => 'alert alert-success']));
} else {
    if ($PocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
                _('Nemáte definovaný žádné služby'),
                ['class' => 'alert alert-info']));
            $warning->addItem(new \Ease\TWB\LinkButton('service.php',
                _('Založit první službu').' <i class="icon-edit"></i>'));
        } else {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
                _('Služby vyžadují časovou periodu ..'),
                ['class' => 'alert alert-danger']));
        }
    } else {
        $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
            _('Služby vyžadují příkazy ..'), ['class' => 'alert alert-danger']));
    }
}

$serviceGroup     = new Engine\IEServicegroup();
$pocServicegroups = $serviceGroup->getMyRecordsCount();
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Servicegroup',
        new \Ease\TWB\LinkButton('servicegroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin služeb'),
            $pocServicegroups)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host',
        _('Nemáte definovaný žádné skupiny služeb'),
        ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('servicegroup.php',
        _('Založit první skupinu služeb').' <i class="icon-edit"></i>'));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
