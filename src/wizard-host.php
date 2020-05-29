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

$host_name = $oPage->getRequestValue('host_name');
$platform = $oPage->getRequestValue('platform');
$host_group = $oPage->getRequestValue('host_group', 'int');
$check_method = $oPage->getRequestValue('check_method', 'int');
$host_is_server = $oPage->getRequestValue('host_is_server', 'boolean');

$host = new Engine\Host($host_name);

if ($host->getId()) {
    $host->addStatusMessage(_('This hostname allready exists'), 'warning');
} else {
    if ($oPage->isPosted()) {
        if ($host_name) {
            if ($check_method) {
                $oPage->redirect('wizard-active-host.php?host_name=' . urlencode($host_name) . '&host_group=' . $host_group . '&platform=' . $platform . '&host_is_server=' . $host_is_server);
            } else {
                $oPage->redirect('wizard-passive-host.php?host_name=' . urlencode($host_name) . '&host_group=' . $host_group . '&platform=' . $platform . '&host_is_server=' . $host_is_server);
            }
        } else {
            $host->addStatusMessage(_('Host name is required'), 'warning');
        }
    }
}
$oPage->addItem(new UI\PageTop(_('New Host Wizard')));


$newHostForm = new UI\ColumnsForm(['name'=>'newhost']);
$newHostForm->addInput(new \Ease\Html\InputTextTag('host_name', $host_name),
        _('Name'), _('Host Name'), _('Unique identificator'));
$newHostForm->addInput(new UI\TWBSwitch('check_method', $check_method, true,
                ['handleWidth' => '200px', 'onText' => _('Active'), 'offText' => _('Passive')]),
        _('Watch method'), _('Host watch method'),
        _('<strong>Active</strong> tracked guests require Icinga to be able to reach tested machine. <br>Hosts checked <strong>Passive</strong> sends its tests results to Icinga server '));
$newHostForm->addItem(new \Ease\TWB\FormGroup(_('Platform'),
                new UI\PlatformSelector('platform'), null, _('Watched host platform')));

$newHostForm->addInput(new UI\HostgroupSelect('host_group', null, $host_group),
        _('Hostgroup'), _('Initial hostgroup for Host'), _('Optional'));

$newHostForm->addInput(new UI\TWBSwitch('host_is_server', $check_method, true,
                ['handleWidth' => '200px', 'onText' => _('Yes'), 'offText' => _('No')]),
        _('Still running'), _('Still running ?'),
        _('<strong>Yes</strong> host is still Up. <br><strong>No</strong> device every night down (notebook or PC etc.)'));



$newHostForm->addItem(new \Ease\TWB\SubmitButton(_('Create'), 'success'));

$oPage->container->addItem(new \Ease\TWB\Panel(_('New host'), 'default',
                $newHostForm));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
