<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - contacts
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$timeperiod     = new Engine\Timeperiod();
$pocTimeperiods = $timeperiod->getMyRecordsCount();
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Timeperiod',
            new \Ease\TWB\LinkButton('timeperiods.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s timeperiods'),
                    $pocTimeperiods)), ['class' => 'alert alert-success']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
            _('No timeperiods defined'),
            ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('timeperiod.php',
            _('Create first timeperiod').' <i class="icon-edit"></i>'));
}

$contact    = new Engine\Contact();
$pocContact = $contact->getMyRecordsCount();
if ($pocContact) {
    $success = $oPage->columnII->addItem(new \Ease\Html\DivTag(
            new \Ease\TWB\LinkButton('contacts.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s contacts defined'),
                    $pocContact)),
            ['class' => 'alert alert-success', 'id' => 'Contact']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new \Ease\Html\Div(
                _('No contact defined'),
                ['class' => 'alert alert-info', 'id' => 'Contact']));
        $warning->addItem(new \Ease\TWB\LinkButton('contact.php',
                _('Create first contact ').\Ease\TWB\Part::GlyphIcon('edit')));
    } else {
        $oPage->columnII->addItem(new \Ease\Html\DivTag(
                _('timeperiod is required for creating a contact'),
                ['class' => 'alert alert-danger']));
    }
}

$contactgroup    = new Engine\Contactgroup();
$pocContactgroup = $contactgroup->getMyRecordsCount();
if ($pocContactgroup) {
    $success = $oPage->columnII->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('contactgroups.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s contactgroups defined'),
                    $pocContactgroup)),
            ['class' => 'alert alert-success', 'id' => 'Contactgroup']));
} else {
    $warning = $oPage->columnII->addItem(new \Ease\Html\Div(
            _('No contactgroup defined'),
            ['class' => 'alert alert-info', 'id' => 'Contactgroup']));
    $warning->addItem(new \Ease\TWB\LinkButton('contactgroup.php',
            _('Create first contactgroup ').\Ease\TWB\Part::GlyphIcon('edit')));
}

$host     = new Engine\Host();
$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnI->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('hosts.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s hosts defined'),
                    $pocHostu)),
            ['class' => 'alert alert-success', 'id' => 'Host']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnI->addItem(new \Ease\Html\Div(
                _('No host defined'),
                ['class' => 'alert alert-info', 'id' => 'Host']));
        $warning->addItem(new \Ease\TWB\LinkButton('host.php',
                _('Create first host').' '.\Ease\TWB\Part::GlyphIcon('edit')));
    } else {
        $warning = $oPage->columnI->addItem(new \Ease\Html\Div(
                _('Timeperiod is required for host'),
                ['class' => 'alert alert-danger', 'id' => 'Host']));
    }
}

$hostgroup     = new Engine\Hostgroup();
$pocHostgroups = $hostgroup->getMyRecordsCount();
if ($pocHostgroups) {
    $success = $oPage->columnI->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('hostgroups.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s hostgroup defined'),
                    $pocHostgroups)),
            ['class' => 'alert alert-success', 'id' => 'Hostgroup']));
} else {
    $warning = $oPage->columnI->addItem(new \Ease\Html\DivTag(
            _('No hostgroup defined'),
            ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('hostgroup.php',
            _('Create first hostgroup').' <i class="icon-edit"></i>'));
}

$command     = new Engine\Command();
$pocCommands = $command->getMyRecordsCount();
if ($pocCommands) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('commands.php',
                '<i class="icon-list"></i> '.sprintf(_('%s commands defined'),
                    $pocCommands)),
            ['class' => 'alert alert-success', 'id' => 'Command']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
            _('No commands defined'),
            ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('importcommand.php',
            _('Import commands').' <i class="icon-download"></i>'));
}

$service     = new Engine\Service();
$pocServices = $service->getMyRecordsCount();
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('services.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s services defined'),
                    $pocServices)),
            ['class' => 'alert alert-success', 'id' => 'Service']));
} else {
    if ($pocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
                    _('No services defined'),
                    ['class' => 'alert alert-info', 'id' => 'Host']));
            $warning->addItem(new \Ease\TWB\LinkButton('service.php',
                    _('Create first service').' <i class="icon-edit"></i>'));
        } else {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
                    _('Timeperiod is requied for service'),
                    ['class' => 'alert alert-danger', 'id' => 'Host']));
        }
    } else {
        $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
                _('Commands requied for service'),
                ['class' => 'alert alert-danger', 'id' => 'Host']));
    }
}

$serviceGroup     = new Engine\Servicegroup();
$pocServicegroups = $serviceGroup->getMyRecordsCount();
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
            new \Ease\TWB\LinkButton('servicegroups.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s servicegroup defined'),
                    $pocServicegroups)),
            ['class' => 'alert alert-success', 'id' => 'Servicegroup']));
} else {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\Div(
            _('No servicegroup defined'),
            ['class' => 'alert alert-info', 'id' => 'Host']));
    $warning->addItem(new \Ease\TWB\LinkButton('servicegroup.php',
            _('Create first servicegroup').' <i class="icon-edit"></i>'));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
