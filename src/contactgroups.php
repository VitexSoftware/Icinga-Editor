<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled kontaktů
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Contactgroups')));

$oPage->container->addItem(new UI\DataGrid(_('Contactgroups'),
                new Engine\Contactgroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
