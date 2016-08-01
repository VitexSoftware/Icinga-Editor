<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$file = $oPage->getRequestValue('file');
$line = $oPage->getRequestValue('line');

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$lines = file($file);
foreach ($lines as $line) {
    $oPage->addItem($line.'<br>');
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
