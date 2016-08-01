<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled skupin služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Přehled skupin služeb')));

$oPage->container->addItem(new UI\DataGrid(_('Skupiny služeb'),
    new Engine\Servicegroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
