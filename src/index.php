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
require_once 'includes/IEInit.php';

if ($oUser->getUserId()) {
    $oPage->redirect('main.php');
    exit;
}

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$oPage->heroUnit = $oPage->container->addItem(new \Ease\Html\Div(null,
    ['id' => 'heroUnit', 'class' => 'jumbotron']));
$oPage->heroUnit->addItem(new \Ease\Html\ImgTag('img/vsmonitoring.png'));
$oPage->heroUnit->addItem(new \Ease\Html\ATag('http://icinga.org/',
    new \Ease\Html\ImgTag('img/icinga_logo4-300x109.png')));
$oPage->heroUnit->addItem(_('Monitoring služeb'));
$oPage->heroUnit->setTagCss(['text-align' => 'center']);

$oPage->columnI->addItem(_('Sledování hostů'));
$oPage->columnII->addItem(_('Sledování služeb'));
$oPage->columnIII->addItem(_('Notifikace mail/jabber/sms/twitter'));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
