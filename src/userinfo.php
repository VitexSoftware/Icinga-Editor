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

$oPage->addPageColumns();

$userID = $oPage->getRequestValue('user_id', 'int');
if ($userID) {
    $oPage->onlyForAdmin();
}

$host = new IEHost();
$hostgroup = new IEHostgroup();
$contact = new IETimeperiod();
$command = new IECommand();
$service = new IEService();
$serviceGroup = new IEServicegroup();


$ownership = $oPage->getRequestValue('ownership');

if ($ownership) {
    $host->switchOwners($userID, $ownership);
    $hostgroup->switchOwners($userID, $ownership);
    $contact->switchOwners($userID, $ownership);
    $command->switchOwners($userID, $ownership);
    $service->switchOwners($userID, $ownership);
    $serviceGroup->switchOwners($userID, $ownership);
    $oPage->addStatusMessage(_('Vlastnictví byla předána'));
}

$user = new IEUser($userID);

$oPage->addItem(new UI\PageTop($user->getUserName()));


if ($oPage->getRequestValue('delete') == 'true') {
    if ($user->delete()) {
        $oPage->redirect('users.php');
        exit();
    }
}

$userInfoFrame = $oPage->columnI->addItem(new \Ease\TWB\Panel($user->getUserLogin()));
$userInfoFrame->addItem($user);
$userInfoFrame->addItem(new \Ease\Html\UlTag(array($user->getUserName(), new \Ease\Html\ATag('mailto:' . $user->getEmail(), $user->getEmail()))));


$pocTimeperiods = $contact->getMyRecordsCount($userID);
if ($pocTimeperiods) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Timeperiod', new \Ease\TWB\LinkButton('timeperiods.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s časových period'), $pocTimeperiods)), array('class' => 'alert alert-success')));
}

$pocHostu = $host->getMyRecordsCount($userID);
if ($pocHostu) {
    $success = $oPage->columnII->addItem(new \Ease\Html\DivTag('Host', new \Ease\TWB\LinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
} else {
    if ($pocTimeperiods) {
        $warning = $oPage->columnII->addItem(new \Ease\Html\DivTag('Host', _('Nemáte definovaný žádný host'), array('class' => 'alert alert-info')));
        $warning->addItem(new \Ease\TWB\LinkButton('host.php', _('Založit první host') . ' ' . \Ease\TWB\Part::GlyphIcon('edit')));
    }
}

$pocHostgroups = $hostgroup->getMyRecordsCount($userID);
if ($pocHostgroups) {
    $success = $oPage->columnII->addItem(new \Ease\Html\DivTag('Hostgroup', new \Ease\TWB\LinkButton('hostgroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin hostů'), $pocHostgroups)), array('class' => 'alert alert-success')));
}

$PocCommands = $command->getMyRecordsCount($userID);
if ($PocCommands) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Command', new \Ease\TWB\LinkButton('commands.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s příkazů'), $PocCommands)), array('class' => 'alert alert-success')));
}

$pocServices = $service->getMyRecordsCount($userID);
if ($pocServices) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Service', new \Ease\TWB\LinkButton('services.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s služeb'), $pocServices)), array('class' => 'alert alert-success')));
} else {
    if ($PocCommands) {
        if ($pocTimeperiods) {
            $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Host', _('Nemáte definovaný žádné služby'), array('class' => 'alert alert-info')));
            $warning->addItem(new \Ease\TWB\LinkButton('service.php', _('Založit první službu') . ' <i class="icon-edit"></i>'));
        }
    }
}

$pocServicegroups = $serviceGroup->getMyRecordsCount($userID);
if ($pocServicegroups) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Servicegroup', new \Ease\TWB\LinkButton('servicegroups.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s skupin služeb'), $pocServicegroups)), array('class' => 'alert alert-success')));
}

if ($oUser->getSettingValue('admin')) {
    $oPage->columnI->addItem(new \Ease\TWB\LinkButton('login.php?force_id=' . $userID, _('Přihlásit se jako uživatel <i class="icon-refresh"></i>')));
}


if ($oUser->getSettingValue('admin') || ($oUser->getId() == $userID)) {
    $ownershipForm = new \Ease\TWB\Form('ownershipForm', null, 'POST');
    $ownershipForm->addInput(
        new IEUserSelect('ownership'), _('Nový vlastník')
    );
    $ownershipForm->addItem(new \Ease\TWB\SubmitButton(_('Předat'), 'warning'));
    $oPage->columnII->addItem(
        new \Ease\TWB\Panel(_('Předat vlastnictví'), 'warning', $ownershipForm)
    );
}

$oPage->columnIII->addItem($user->deleteButton());

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
