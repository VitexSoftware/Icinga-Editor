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
require_once 'classes/IEDbFixer.php';


$oPage->addItem(new UI\PageTop(_('Oprava databaze')));
$oPage->onlyForLogged();

$loginFace = new \Ease\Html\DivTag('LoginFace');

$oPage->addItem(new \Ease\TWB\Container(new \Ease\TWB\Panel(_('Oprava databáze'), 'warning', new IEDbFixer)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
