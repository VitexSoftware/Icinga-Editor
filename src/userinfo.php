<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - user informations
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addPageColumns();

$userID = $oPage->getRequestValue('user_id', 'int');
if ($userID) {
    $oPage->onlyForAdmin();
}

$host         = new Engine\Host();
$hostgroup    = new Engine\Hostgroup();
$contact      = new Engine\Timeperiod();
$command      = new Engine\Command();
$service      = new Engine\Service();
$serviceGroup = new Engine\Servicegroup();


$ownership = $oPage->getRequestValue('ownership');

if ($ownership) {
    $host->switchOwners($userID, $ownership);
    $hostgroup->switchOwners($userID, $ownership);
    $contact->switchOwners($userID, $ownership);
    $command->switchOwners($userID, $ownership);
    $service->switchOwners($userID, $ownership);
    $serviceGroup->switchOwners($userID, $ownership);
    $oPage->addStatusMessage(_('Ownership was handed over'));
}

$user = new User($userID);

$oPage->addItem(new UI\PageTop($user->getUserName()));


if ($oPage->getRequestValue('delete') == 'true') {
    if ($user->delete()) {
        $oPage->redirect('users.php');
        exit();
    }
}

$userInfoFrame = $oPage->columnI->addItem(new \Ease\TWB\Panel($user->getUserLogin()));
$userInfoFrame->addItem($user);
$userInfoFrame->addItem(new \Ease\Html\UlTag([$user->getUserName(), new \Ease\Html\ATag('mailto:'.$user->getEmail(),
        $user->getEmail())]));


$pocTimeperiods = $contact->getMyRecordsCount($userID);
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('timeperiods.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Defined %s time periods'),
                    $pocTimeperiods)),
        ['class' => 'alert alert-success', 'id' => 'Timeperiod']));
}

$pocHostu = $host->getMyRecordsCount($userID);
if ($pocHostu) {
    $success = $oPage->columnII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('hosts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('%s hosts defined'),
                    $pocHostu)), ['class' => 'alert alert-success', 'id' => 'Host']));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new \Ease\Html\Div(
            _('No host defined'),
                ['class' => 'alert alert-info', 'id' => 'Host']));
        $warning->addItem(new \Ease\TWB\LinkButton('host.php',
            _('Create first host').' '.\Ease\TWB\Part::GlyphIcon('edit')));
    }
}

$pocHostgroups = $hostgroup->getMyRecordsCount($userID);
if ($pocHostgroups) {
    $success = $oPage->columnII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('hostgroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('%s hostgroups defined'),
                    $pocHostgroups)),
        ['class' => 'alert alert-success', 'id' => 'Hostgroup']));
}

$pocCommands = $command->getMyRecordsCount($userID);
if ($pocCommands) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('commands.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('%s commands defined'),
                    $pocCommands)),
        ['class' => 'alert alert-success', 'id' => 'Command']));
}

$pocServices = $service->getMyRecordsCount($userID);
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
        }
    }
}

$pocServicegroups = $serviceGroup->getMyRecordsCount($userID);
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('servicegroups.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('%s servicegrops defined'),
                    $pocServicegroups)),
        ['class' => 'alert alert-success', 'id' => 'Servicegroup']));
}

if ($oUser->getSettingValue('admin')) {
    $oPage->columnI->addItem(new \Ease\TWB\LinkButton('login.php?force_id='.$userID,
        _('Logn as user').' <i class="icon-refresh"></i>'));
}


if ($oUser->getSettingValue('admin') || ($oUser->getId() == $userID)) {
    $ownershipForm = new \Ease\TWB\Form('ownershipForm', null, 'POST');
    $ownershipForm->addInput(
        new UI\UserSelect('ownership'), _('New owner')
    );
    $ownershipForm->addItem(new \Ease\TWB\SubmitButton(_('Hand over ownership'),
            'warning'));
    $oPage->columnII->addItem(
        new \Ease\TWB\Panel(_('Hand over'), 'warning', $ownershipForm)
    );
}

$oPage->columnIII->addItem($user->deleteButton());

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
