<?php

namespace Flexplorer;

namespace Icinga\Editor;

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->addItem(new UI\PageTop(_('O Aplikaci')));

$oPage->container->addItem('<br/><br/><br/><br/>');
$oPage->container->addItem(new \Ease\Html\Div(nl2br(file_get_contents('../README.md')),
    ['class' => 'jumbotron']));
$oPage->container->addItem('<br/><br/><br/><br/>');

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
