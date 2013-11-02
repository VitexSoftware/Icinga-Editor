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

$OPage->addItem(new IEPageTop(_('Icinga Editor')));

$OPage->heroUnit->addItem( new EaseHtmlImgTag('img/vsmonitoring.png') );
$OPage->heroUnit->setTagCss(array('text-align'=>'center'));
        
$OPage->column1->addItem(_('Sledování hostů'));
$OPage->column2->addItem(_('Sledování služeb'));
$OPage->column3->addItem(_('Notifikace mail/jabber/sms/twitter'));


$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
