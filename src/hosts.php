<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - hosts overview
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Hosts Overview')));

$oPage->addItem(new \Ease\TWB\Container(new UI\DataGrid(_('Hosts'),
            new Engine\Host)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
