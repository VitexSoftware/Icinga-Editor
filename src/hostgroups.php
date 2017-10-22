<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled skupin hostů
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Hostgroup overview')));

$oPage->container->addItem(new UI\DataGrid(_('Hostgroups'), new Engine\Hostgroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
