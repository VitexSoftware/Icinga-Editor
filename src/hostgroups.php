<?php

/**
 * Icinga Editor - přehled skupin hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHostgroup.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled skupin hostů')));

$oPage->container->addItem(new IEDataGrid(_('Skupiny hostů'), new IEHostgroup));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
