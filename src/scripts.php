<?php

/**
 * Icinga Editor - přehled příkazů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEScript.php';
require_once 'classes/IEDataGrid.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled skriptů')));

$oPage->addItem(new EaseTWBContainer(new IEDataGrid(_('Skripty'), new IEScript)));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
