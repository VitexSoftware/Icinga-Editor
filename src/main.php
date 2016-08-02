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

$oPage->addItem(new UI\PageTop(_('Monitoring')));

$host  = new engine\Host();
$hosts = $host->getListing(null, false,
    ['config_hash', 'address', 'parents', 'icon_image', 'contacts', 'contact_groups',
    $host->myCreateColumn, $host->myLastModifiedColumn]);

if (count($hosts)) {
    $oPage->container->addItem(new UI\ConfigurationsOverview($hosts));
} else {
    $oPage->container->addItem(new \Ease\TWB\LinkButton('wizard-host.php',
        _('Založte si první sledovaný host'), 'success'));
    $oUser->addStatusMessage(_('It is not yet registered any monitored host'),
        'warning');
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
