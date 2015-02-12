<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContact.php';
require_once 'classes/IEContactgroup.php';
require_once 'classes/IEHost.php';
require_once 'classes/IEHostgroup.php';
require_once 'classes/IETimeperiod.php';
require_once 'classes/IECommand.php';
require_once 'classes/IEServicegroup.php';

$oPage->onlyForLogged();

$File = $oPage->getRequestValue('file');
$line = $oPage->getRequestValue('line');

$oPage->addItem(new IEPageTop(_('Icinga Editor')));

$Lines = file($File);
foreach ($Lines as $line) {
    $oPage->addItem($line.'<br>');
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
