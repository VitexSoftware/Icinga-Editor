<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled příkazů
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Watched service templates overview')));

$oPage->addItem(new \Ease\TWB\Container(new UI\DataGrid(_('Watched service overview'),
                        new Stemplate)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
