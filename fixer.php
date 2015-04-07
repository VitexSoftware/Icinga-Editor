<?php

/**
 * Přihlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEDbFixer.php';


$oPage->addItem(new IEPageTop(_('Oprava databaze')));
$oPage->onlyForLogged();

$loginFace = new EaseHtmlDivTag('LoginFace');

$oPage->addItem(new EaseTWBContainer(new EaseTWBPanel(_('Oprava databáze'), 'warning', new IEDbFixer)));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
