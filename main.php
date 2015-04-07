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

$oPage->addItem(new IEPageTop(_('Monitoring')));

$host = new IEHost();
$hosts = $host->getListing(null, false, array('config_hash', $host->myCreateColumn, $host->myLastModifiedColumn));

if (count($hosts)) {
    $oPage->container->addItem(new IEConfigurationsOverview($hosts));
} else {
    $oPage->container->addItem(new EaseTWBLinkButton('wizard.php', _('Založte si první sledovaný host'), 'success'));
    $oUser->addStatusMessage(_('Zatím není zaregistrovaný žádný sledovaný host'), 'warning');
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
