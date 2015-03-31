<?php

/**
 * Icinga Editor - reset objektů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->onlyForAdmin();

if ($oPage->isPosted()) {
    if ($oPage->getRequestValue('host')) {
        EaseShared::db()->exeQuery('DELETE FROM `host` WHERE register=1');
        EaseShared::db()->exeQuery('UPDATE `service` SET host_name=\'a:0:{}\'');
        $oPage->addStatusMessage(_('Hosti odstraněni'), 'success');
    }
    if ($oPage->getRequestValue('hostgroup')) {
        EaseShared::db()->exeQuery('TRUNCATE TABLE `hostgroup`');
        $oPage->addStatusMessage(_('Skupiny hostů  byly odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('contact')) {
        EaseShared::db()->exeQuery('DELETE FROM `contact` WHERE register=1');
        $oPage->addStatusMessage(_('Kontakty odstraněni'), 'success');
    }
    if ($oPage->getRequestValue('contactgroup')) {
        EaseShared::db()->exeQuery('TRUNCATE TABLE `contactgroup`');
        $oPage->addStatusMessage(_('Skupiny kontaktů  byly odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('service')) {
        EaseShared::db()->exeQuery('DELETE FROM `service` WHERE register=1');
        $oPage->addStatusMessage(_('Služby odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('servicegroup')) {
        EaseShared::db()->exeQuery('TRUNCATE TABLE `servicegroup`');
        $oPage->addStatusMessage(_('Skupiny služeb byly odstraněny'), 'success');
    }
}


$oPage->addItem(new IEPageTop(_('Reset objekt')));

$resetForm = new EaseTWBForm('reset');
$resetForm->addInput(new IEYesNoSwitch('host', FALSE), _('Hosti'), null, _('Smaže hosty, ale nechá předlohy'));
$resetForm->addInput(new IEYesNoSwitch('hostgroup', FALSE), _('Skupiny hostů'), null, _('Smaže skupiny hostů'));
$resetForm->addInput(new IEYesNoSwitch('contact', FALSE), _('Kontakty'), null, _('Smaže kontakty'));
$resetForm->addInput(new IEYesNoSwitch('contactgroup', FALSE), _('Skupiny kontaktů'), null, _('Smaže skupiny kontaktů'));
$resetForm->addInput(new IEYesNoSwitch('service', FALSE), _('Služby'), null, _('Smaže služby'));
$resetForm->addInput(new IEYesNoSwitch('servicegroup', FALSE), _('Skupiny služeb'), null, _('Smaže skupiny služeb'));

$resetForm->addItem(new EaseTWSubmitButton(_('Vymazat všechna data'), 'danger'));

$container = $oPage->addItem(new EaseTWBContainer);
$container->addItem(new EaseTWBPanel(_('Pročištění databáze'), 'danger', new EaseTWBWell($resetForm)));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
