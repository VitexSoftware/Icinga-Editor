<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));

IEcfg::reloadIcinga();

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
