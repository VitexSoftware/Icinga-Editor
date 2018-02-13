<?php

namespace Icinga\Editor;

/**
 * Přihlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';


$oPage->addItem(new UI\PageTop(_('Database fixer')));
$oPage->onlyForLogged();

$oPage->addItem(new \Ease\TWB\Container(new \Ease\TWB\Panel(_('Database fix'),
            'warning', new DbFixer())));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
