<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - přehled skupin hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Přehled skupin hostů')));

$oPage->container->addItem(new UI\DataGrid(_('Skupiny hostů'), new Engine\Hostgroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
