<?php

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
$host_group = $oPage->getRequestValue('host_group', 'int');
$check_method = $oPage->getRequestValue('check_method', 'int');

$host = new IEHost($host_name);

if ($host->getId()) {
    $host->addStatusMessage(_('Host tohoto jména již existuje'), 'warning');
} else {
    if ($oPage->isPosted()) {
        if ($host_name) {
            if ($check_method) {
                $oPage->redirect('wizard-active-host.php?host_name=' . urlencode($host_name) . '&host_group=' . $host_group);
            } else {
                $oPage->redirect('wizard-passive-host.php?host_name=' . urlencode($host_name) . '&host_group=' . $host_group);
            }
        } else {
            $host->addStatusMessage(_('Není zadáno jméno hosta'), 'warning');
        }
    }
}
$oPage->addItem(new IEPageTop(_('Průvodce založením hosta')));



$newHostForm = new EaseTWBForm('newhost');
$newHostForm->addInput(new EaseHtmlInputTextTag('host_name', $host_name), _('Název'), _('Název sledovaného hostu'), _('Jedinečný identifikátor'));
$newHostForm->addInput(new IETWBSwitch('check_method', $check_method, true, array('handleWidth' => '200px', 'onText' => _('Aktivní'), 'offText' => _('Pasivní'))), _('Metoda sledování'), _('Metoda sledování hostu'), _('<strong>Aktivně</strong> sledované hosty vyžadují aby byla icinga schopná dosáhnout na testovaný stroj. <br><strong>Pasivně</strong> sledovaný host zasílá sám na server kde běží icinga výsledky testů '));
$newHostForm->addInput(new IEHostgroupSelect('host_group', null, $host_group), _('Skupina'), _('Výchozí skupina sledovanéh hostu'), _('Tato volba není povinná'));

$newHostForm->addItem(new EaseTWSubmitButton(_('Založit'), 'success'));

$oPage->container->addItem(new EaseTWBPanel(_('Nový host'), 'default', $newHostForm));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
