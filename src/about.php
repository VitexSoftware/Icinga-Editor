<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - hlavní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
use League\CommonMark\CommonMarkConverter;

require_once 'includes/IEInit.php';


$oPage->addItem(new UI\PageTop(_('About Application')));

$oPage->container->addItem(_('Used Libraries').':');
$oPage->container->addItem('<br> EasePHP Framework v'.\Ease\Atom::$frameworkVersion);

$oPage->container->addItem('<br/><br/><br/><br/>');
$converter = new CommonMarkConverter();

$oPage->container->addItem(new \Ease\Html\DivTag($converter->convertToHtml(file_get_contents('../README.md')),
        ['class' => 'jumbotron']));
$oPage->container->addItem('<br/><br/><br/><br/>');

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
