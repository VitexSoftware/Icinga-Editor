<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Objects Database Reset to empty
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->onlyForAdmin();

if ($oPage->isPosted()) {
    $cc = \Ease\Shared::db()->getColumnComma();
    if ($oPage->getRequestValue('host')) {
        \Ease\Shared::db()->exeQuery('DELETE FROM '.$cc.'host'.$cc.' WHERE register=1');
        \Ease\Shared::db()->exeQuery('UPDATE '.$cc.'service'.$cc.' SET host_name=\'a:0:{}\'');
        $oPage->addStatusMessage(_('Hosts Removed'), 'success');
    }
    if ($oPage->getRequestValue('hostgroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE '.$cc.'hostgroup'.$cc.'');
        $oPage->addStatusMessage(_('Hostgroups removed'), 'success');
    }
    if ($oPage->getRequestValue('contact')) {
        \Ease\Shared::db()->exeQuery('DELETE FROM '.$cc.'contact'.$cc.' WHERE register=1');
        $oPage->addStatusMessage(_('Contacts removed'), 'success');
    }
    if ($oPage->getRequestValue('contactgroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE '.$cc.'contactgroup'.$cc.'');
        $oPage->addStatusMessage(_('Contactgroups removed'), 'success');
    }
    if ($oPage->getRequestValue('service')) {
        \Ease\Shared::db()->exeQuery('DELETE FROM '.$cc.'service'.$cc.' WHERE register=1');
        $oPage->addStatusMessage(_('Services removed'), 'success');
    }
    if ($oPage->getRequestValue('servicegroup')) {
        \Ease\Shared::db()->exeQuery('TRUNCATE TABLE '.$cc.'servicegroup'.$cc.'');
        $oPage->addStatusMessage(_('Servicegroups removed'), 'success');
    }
    if ($oPage->getRequestValue('desync')) {
        \Ease\Shared::db()->exeQuery('UPDATE '.$cc.'host'.$cc.' SET config_hash = 0');
        $oPage->addStatusMessage(_('Sensors unsynced'), 'success');
    }
    if ($oPage->getRequestValue('sync')) {
        $host     = new Engine\Host;
        $allHosts = $host->getListing();
        foreach ($allHosts as $hostId => $hostInfo) {
            $host->dataReset();
            $host->loadFromSQL((int) $hostId);
            $host->setDataValue('config_hash', $host->getConfigHash());
            $host->saveToSQL();
        }
        $oPage->addStatusMessage(sprintf(_('%s sensor states was set'),
                count($allHosts)), 'success');
    }
}


$oPage->addItem(new UI\PageTop(_('Objects Database Reset')));

$resetForm = new \Ease\TWB\Form('reset');
$resetForm->addInput(new UI\YesNoSwitch('host', FALSE), _('Hosti'), null,
    _('Remove hosts, but keep templates'));
$resetForm->addInput(new UI\YesNoSwitch('hostgroup', FALSE), _('Hostgroups'),
    null, _('Remove hostgroups'));
$resetForm->addInput(new UI\YesNoSwitch('contact', FALSE), _('Contacts'), null,
    _('Remove Contacts'));
$resetForm->addInput(new UI\YesNoSwitch('contactgroup', FALSE),
    _('Contactgroups'), null, _('Remove Contactgroups'));
$resetForm->addInput(new UI\YesNoSwitch('service', FALSE), _('Services'), null,
    _('Remove Services'));
$resetForm->addInput(new UI\YesNoSwitch('servicegroup', FALSE),
    _('Servicegroups'), null, _('Remove Servicegroups'));

$resetForm->addItem(new \Ease\TWB\SubmitButton(_('Remove all Data'), 'danger'));

$toolRow = new \Ease\TWB\Row;
$toolRow->addColumn(6, new \Ease\TWB\Well($resetForm));

$resyncForm = new \Ease\TWB\Form('resync');
$resyncForm->addInput(new UI\YesNoSwitch('desync', FALSE), _('Unsync Hash'),
    null, _('Make all configurations obsolete'));
$resyncForm->addInput(new UI\YesNoSwitch('sync', FALSE), _('Sync Hash'), null,
    _('Make all configurations actual'));
$resyncForm->addItem(new \Ease\TWB\SubmitButton(_('Perform operation'),
        'warning', ['onClick' => "$('#preload').css('visibility', 'visible');"]));
$toolRow->addColumn(6, new \Ease\TWB\Well($resyncForm));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Database cleaning'), 'danger',
        $toolRow));
\Ease\Shared::webPage()->addItem(new \Ease\Html\Div(
        new UI\FXPreloader(), ['class' => 'fuelux', 'id' => 'preload']));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
