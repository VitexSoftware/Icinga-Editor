<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - přehled hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEDataGrid.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Přehled hostů')));

$oPage->addItem(new \Ease\TWB\Container(new IEDataGrid(_('Hosti'), new IEHost)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
