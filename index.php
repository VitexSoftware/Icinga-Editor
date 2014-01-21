<?php

/**
 * Icinga Editor - hlavní strana
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->addItem(new IEPageTop(_('Icinga Editor')));

$oPage->heroUnit->addItem( new EaseHtmlImgTag('img/vsmonitoring.png') );
$oPage->heroUnit->setTagCss(array('text-align'=>'center'));
        
$oPage->columnI->addItem(_('Sledování hostů'));
$oPage->columnII->addItem(_('Sledování služeb'));
$oPage->columnIII->addItem(_('Notifikace mail/jabber/sms/twitter'));


$oPage->addItem(new IEPageBottom());


$oPage->draw();
