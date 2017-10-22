<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled skupin služeb
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Service group overview')));

$oPage->container->addItem(new UI\DataGrid(_('Servicegroups'),
        new Engine\Servicegroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
