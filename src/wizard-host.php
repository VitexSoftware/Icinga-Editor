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

$host_name    = $oPage->getRequestValue('host_name');
$platform     = $oPage->getRequestValue('platform');
$host_group   = $oPage->getRequestValue('host_group', 'int');
$check_method = $oPage->getRequestValue('check_method', 'int');

$host = new Engine\Host($host_name);

if ($host->getId()) {
    $host->addStatusMessage(_('Host tohoto jména již existuje'), 'warning');
} else {
    if ($oPage->isPosted()) {
        if ($host_name) {
            if ($check_method) {
                $oPage->redirect('wizard-active-host.php?host_name='.urlencode($host_name).'&host_group='.$host_group.'&platform='.$platform);
            } else {
                $oPage->redirect('wizard-passive-host.php?host_name='.urlencode($host_name).'&host_group='.$host_group.'&platform='.$platform);
            }
        } else {
            $host->addStatusMessage(_('Není zadáno jméno hosta'), 'warning');
        }
    }
}
$oPage->addItem(new UI\PageTop(_('Průvodce založením hosta')));


$newHostForm = new UI\ColumnsForm('newhost');
$newHostForm->addInput(new \Ease\Html\InputTextTag('host_name', $host_name),
    _('Název'), _('Název sledovaného hostu'), _('Jedinečný identifikátor'));
$newHostForm->addInput(new UI\TWBSwitch('check_method', $check_method, true,
    ['handleWidth' => '200px', 'onText' => _('Aktivní'), 'offText' => _('Pasivní')]),
    _('Metoda sledování'), _('Metoda sledování hostu'),
    _('<strong>Aktivně</strong> sledované hosty vyžadují aby byla icinga schopná dosáhnout na testovaný stroj. <br><strong>Pasivně</strong> sledovaný host zasílá sám na server kde běží icinga výsledky testů '));
$newHostForm->addItem(new \Ease\TWB\FormGroup(_('Platforma'),
    new UI\PlatformSelector('platform'), null, _('Platforma sledovaného stroje')));

$newHostForm->addInput(new UI\HostgroupSelect('host_group', null, $host_group),
    _('Skupina'), _('Výchozí skupina sledovanéh hostu'),
    _('Tato volba není povinná'));

$newHostForm->addItem(new \Ease\TWB\SubmitButton(_('Založit'), 'success'));

$oPage->container->addItem(new \Ease\TWB\Panel(_('Nový host'), 'default',
    $newHostForm));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
