<?php

/**
 * Icinga Editor - přehled userů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEDataGrid.php';
require_once 'classes/IEUserGroup.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled uživatelských skupin')));

$oPage->addItem(new EaseTWBContainer(new IEDataGrid(_('Uživatelské skupiny'), new IEUserGroup)));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
