<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - hlavní strana
 * 
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
use League\CommonMark\ConverterInterface;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

require_once 'includes/IEInit.php';


$oPage->addItem(new UI\PageTop(_('About Application')));

$oPage->container->addItem(_('Used Libraries') . ':');
$oPage->container->addItem('<br> EasePHP Framework v' . \Ease\Atom::$frameworkVersion);

$oPage->container->addItem('<br/><br/><br/><br/>');

// Configure environment with security settings
$config = [
    'html_input' => 'strip',  // Strip raw HTML to prevent XSS
    'allow_unsafe_links' => false,  // Disallow unsafe links
];

$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());

$converter = new MarkdownConverter($environment);

$oPage->container->addItem(new \Ease\Html\DivTag($converter->convert(file_get_contents('../README.md')),
                ['class' => 'jumbotron']));
$oPage->container->addItem('<br/><br/><br/><br/>');

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
