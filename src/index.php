<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - index page
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
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
$oPage->heroUnit->addItem(_('Editor'));
$oPage->heroUnit->setTagCss(['text-align' => 'center']);

$oPage->columnI->addItem(_('Watch the hosts'));
$oPage->columnII->addItem(_('Watch the services'));
$oPage->columnIII->addItem(_('Notifications on mail/jabber/sms/twitter'));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
