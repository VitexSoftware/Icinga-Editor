<?php

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEcfg.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Icinga Editor')));

IECfg::reloadIcinga();

$oPage->addItem(new IEPageBottom());

$oPage->draw();
