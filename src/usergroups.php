<?php
namespace Icinga\Editor;

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

$oPage->addItem(new UI\PageTop(_('Přehled uživatelských skupin')));

$oPage->addItem(new \Ease\TWB\Container(new IEDataGrid(_('Uživatelské skupiny'), new IEUserGroup)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
