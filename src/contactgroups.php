<?php

/**
 * Icinga Editor - přehled kontaktů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContactgroup.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Kontaktní skupiny')));

$oPage->container->addItem(new IEDataGrid(_('Kontaktní skupiny'), new IEContactgroup));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
