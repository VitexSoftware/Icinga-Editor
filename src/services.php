<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - services overview
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Services overview')));

$oPage->addItem(new \Ease\TWB\Container(new UI\DataGrid(_('Service'),
                        new Engine\Service())));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
