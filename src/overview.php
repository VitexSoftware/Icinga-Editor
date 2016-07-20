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

$Timeperiod     = new Engine\Timeperiod();
$pocTimeperiods = $Timeperiod->getMyRecordsCount();
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Timeperiod',
        new \Ease\TWB\LinkButton('timeperiods.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s časových period'),
            $pocTimeperiods)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
        _('Nemáte definovaný žádné časové periody'),
        ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('timeperiod.php',
        _('Založit první časovou periodu <i class="icon-edit"></i>')));
}

$contact    = new Engine\Contact();
$pocContact = $contact->getMyRecordsCount();
if ($pocContact) {
    $success = $oPage->columnII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('contacts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s kontaktů'),
            $pocContact)), ['class' => 'alert alert-success', 'id' => 'Contact']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new \Ease\Html\Div(
            _('Nemáte definovaný kontakt'),
            ['class' => 'alert alert-info', 'id' => 'Contact']));
        $warning->addItem(new \Ease\TWB\LinkButton('contact.php',
            _('Založit první kontakt '.\Ease\TWB\Part::GlyphIcon('edit'))));
    } else {
        $oPage->columnII->addItem(new \Ease\Html\DivTag('Contact',
            _('Kontakty vyžadují časovou periodu'),
            ['class' => 'alert alert-danger']));
    }
}

$contactgroup    = new Engine\Contactgroup();
$pocContactgroup = $contactgroup->getMyRecordsCount();
if ($pocContactgroup) {
    $success = $oPage->columnII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('contactgroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin kontaktů'),
            $pocContactgroup)),
        ['class' => 'alert alert-success', 'id' => 'Contactgroup']));
} else {
    $warning = $oPage->columnII->addItem(new \Ease\Html\Div(
        _('Nemáte definovanou skupinu kontaktů'),
        ['class' => 'alert alert-info', 'id' => 'Contactgroup']));
    $warning->addItem(new \Ease\TWB\LinkButton('contactgroup.php',
        _('Založit první skupinu kontaktů '.\Ease\TWB\Part::GlyphIcon('edit'))));
}

$host     = new Engine\Host();
$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnI->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('hosts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s hostů'),
            $pocHostu)), ['class' => 'alert alert-success', 'id' => 'Host']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnI->addItem(new \Ease\Html\Div(
            _('Nemáte definovaný žádný host'),
            ['class' => 'alert alert-info', 'id' => 'Host']));
        $warning->addItem(new \Ease\TWB\LinkButton('host.php',
            _('Založit první host').' '.\Ease\TWB\Part::GlyphIcon('edit')));
    } else {
        $warning = $oPage->columnI->addItem(new \Ease\Html\Div(
            _('Hosty vyžadují časovou periodu ..'),
            ['class' => 'alert alert-danger', 'id' => 'Host']));
    }
}

$hostgroup     = new Engine\Hostgroup();
$pocHostgroups = $hostgroup->getMyRecordsCount();
if ($pocHostgroups) {
    $success = $oPage->columnI->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('hostgroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin hostů'),
            $pocHostgroups)),
        ['class' => 'alert alert-success', 'id' => 'Hostgroup']));
} else {
    $warning = $oPage->columnI->addItem(new \Ease\Html\DivTag(
        _('Nemáte definovaný žádnou skupinu hostů'),
        ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('hostgroup.php',
        _('Založit první skupinu hostů <i class="icon-edit"></i>')));
}

$command     = new Engine\Command();
$PocCommands = $command->getMyRecordsCount();
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('commands.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s příkazů'),
            $PocCommands)),
        ['class' => 'alert alert-success', 'id' => 'Command']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
        _('Nemáte definovaný žádné příkazy'),
        ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('importcommand.php',
        _('Importovat příkazy').' <i class="icon-download"></i>'));
}

$service     = new Engine\Service();
$pocServices = $service->getMyRecordsCount();
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('services.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s služeb'),
            $pocServices)),
        ['class' => 'alert alert-success', 'id' => 'Service']));
} else {
    if ($PocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
                _('Nemáte definovaný žádné služby'),
                ['class' => 'alert alert-info', 'id' => 'Host']));
            $warning->addItem(new \Ease\TWB\LinkButton('service.php',
                _('Založit první službu').' <i class="icon-edit"></i>'));
        } else {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
                _('Služby vyžadují časovou periodu ..'),
                ['class' => 'alert alert-danger', 'id' => 'Host']));
        }
    } else {
        $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
            _('Služby vyžadují příkazy ..'),
            ['class' => 'alert alert-danger', 'id' => 'Host']));
    }
}

$serviceGroup     = new Engine\Servicegroup();
$pocServicegroups = $serviceGroup->getMyRecordsCount();
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('servicegroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s skupin služeb'),
            $pocServicegroups)),
        ['class' => 'alert alert-success', 'id' => 'Servicegroup']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
        _('Nemáte definovaný žádné skupiny služeb'),
        ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('servicegroup.php',
        _('Založit první skupinu služeb').' <i class="icon-edit"></i>'));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
