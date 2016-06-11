<?php

namespace Icinga\Editor;

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
        \Ease\Shared::db()->exeQuery('DELETE FROM `host` WHERE register=1');
        \Ease\Shared::db()->exeQuery('UPDATE `service` SET host_name=\'a:0:{}\'');
        $oPage->addStatusMessage(_('Hosti odstraněni'), 'success');
    }
    if ($oPage->getRequestValue('hostgroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE `hostgroup`');
        $oPage->addStatusMessage(_('Skupiny hostů  byly odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('contact')) {
        \Ease\Shared::db()->exeQuery('DELETE FROM `contact` WHERE register=1');
        $oPage->addStatusMessage(_('Kontakty odstraněni'), 'success');
    }
    if ($oPage->getRequestValue('contactgroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE `contactgroup`');
        $oPage->addStatusMessage(_('Skupiny kontaktů  byly odstraněny'),
            'success');
    }
    if ($oPage->getRequestValue('service')) {
        \Ease\Shared::db()->exeQuery('DELETE FROM `service` WHERE register=1');
        $oPage->addStatusMessage(_('Služby odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('servicegroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE `servicegroup`');
        $oPage->addStatusMessage(_('Skupiny služeb byly odstraněny'), 'success');
    }
    if ($oPage->getRequestValue('desync')) {
        \Ease\Shared::db()->exeQuery('UPDATE `host` SET config_hash = 0');
        $oPage->addStatusMessage(_('Stavy senzorů byly rozhasheny'), 'success');
    }
    if ($oPage->getRequestValue('sync')) {
        $host     = new Engine\IEHost;
        $allHosts = $host->getListing();
        foreach ($allHosts as $hostId => $hostInfo) {
            $host->dataReset();
            $host->loadFromSQL((int) $hostId);
            $host->setDataValue('config_hash', $host->getConfigHash());
            $host->saveToSQL();
        }
        $oPage->addStatusMessage(sprintf(_('Stavy %s senzorů byly nastaveny'),
                count($allHosts)), 'success');
    }
}


$oPage->addItem(new UI\PageTop(_('Reset objekt')));

$resetForm = new \Ease\TWB\Form('reset');
$resetForm->addInput(new UI\YesNoSwitch('host', FALSE), _('Hosti'), null,
    _('Smaže hosty, ale nechá předlohy'));
$resetForm->addInput(new UI\YesNoSwitch('hostgroup', FALSE), _('Skupiny hostů'),
    null, _('Smaže skupiny hostů'));
$resetForm->addInput(new UI\YesNoSwitch('contact', FALSE), _('Kontakty'), null,
    _('Smaže kontakty'));
$resetForm->addInput(new UI\YesNoSwitch('contactgroup', FALSE),
    _('Skupiny kontaktů'), null, _('Smaže skupiny kontaktů'));
$resetForm->addInput(new UI\YesNoSwitch('service', FALSE), _('Služby'), null,
    _('Smaže služby'));
$resetForm->addInput(new UI\YesNoSwitch('servicegroup', FALSE),
    _('Skupiny služeb'), null, _('Smaže skupiny služeb'));

$resetForm->addItem(new \Ease\TWB\SubmitButton(_('Vymazat všechna data'),
    'danger'));

$toolRow = new \Ease\TWB\Row;
$toolRow->addColumn(6, new \Ease\TWB\Well($resetForm));

$resyncForm = new \Ease\TWB\Form('resync');
$resyncForm->addInput(new UI\YesNoSwitch('desync', FALSE), _('Rozhodit Hash'),
    null,
    _('Všechny hosty s nasazeným senzorem budou hlásat zastaralou konfiguraci'));
$resyncForm->addInput(new UI\YesNoSwitch('sync', FALSE), _('Nastavit Hash'),
    null,
    _('Všechny hosty s nasazeným senzorem budou hlásat aktuální konfiguraci'));
$resyncForm->addItem(new \Ease\TWB\SubmitButton(_('Provést operaci'), 'warning',
    ['onClick' => "$('#preload').css('visibility', 'visible');"]));
$toolRow->addColumn(6, new \Ease\TWB\Well($resyncForm));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Pročištění databáze'),
    'danger', $toolRow));
\Ease\Shared::webPage()->addItem(new \Ease\Html\Div(
    new UI\FXPreloader(), ['class' => 'fuelux', 'id' => 'preload']));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
