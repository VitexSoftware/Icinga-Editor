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
require_once 'IEcfg.php';

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Icinga Editor')));

IECfg::reloadIcinga();

$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
