<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - nagstamon
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Nagstamon')));
$oPage->addPageColumns();

$oPage->columnI->addItem(_('Multiplatform client'));

$oPage->columnI->addItem(new \Ease\Html\ATag('http://nagstamon.ifw-dresden.de/',
    new \Ease\Html\ImgTag('img/nagstamon_header_logo.gif')));

$oPage->columnI->addItem(new \Ease\Html\PTag(_('<br>
Nagstamon is a Nagios desktop monitor for desktop operating systems.
Running in the system tray or floating application, or in the status bar at
desktop and shows a brief overview of the host or service state, ie:
critical, alert, unknown, unreachable or off, and shows a detailed overview
status when moving the mouse pointer over it.
Connecting to the displayed hosts and services is easy from the context menu over
SSH, RDP and VNC or any other defined actions.
Users can be alerted by sound.
Hosts and services can be filtered by categories and regular expressions.
')));

$oPage->columnII->addItem(new \Ease\Html\ImgTag('img/nagstamon1.png'));

$oPage->columnI->addItem(new \Ease\Html\ATag('http://nagstamon.ifw-dresden.de/',
    _('Program homepage')));

$oPage->columnI->addItem(new \Ease\Html\H2Tag(_('Setup')));

$oPage->columnII->addItem('<p></p>');
$oPage->columnII->addItem(new \Ease\Html\ImgTag('img/nagstamon2.png'));

$oPage->columnI->addItem('<p>Type: <b>icinga</b>');
$oPage->columnI->addItem('<p>Monitor URL: <b>http://v.s.cz/icinga/</b></p>');
$oPage->columnI->addItem('<p>Monitor Cgi URL: <b>http://v.s.cz/cgi-bin/icinga/</b></p>');
$oPage->columnI->addItem('<p>Username: <b>'.$oUser->getUserLogin().'</b></p>');
$oPage->columnI->addItem('<p>Password: <b>'._('Your password').'</b></p>');

$oPage->columnI->addItem(new \Ease\TWB\LinkButton('settings.php',
    _('Password change'), 'danger'));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
