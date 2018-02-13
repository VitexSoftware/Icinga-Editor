<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - usergroup overview
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Usergroup overview')));

$oPage->addItem(new \Ease\TWB\Container(new UI\DataGrid(_('Usergroups'),
            new Engine\UserGroup)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
