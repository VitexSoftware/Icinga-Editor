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

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Nagstamon')));
$oPage->addPageColumns();

$oPage->columnI->addItem(_('Multiplatformní klient'));

$oPage->columnI->addItem(new EaseHtmlATag('http://nagstamon.ifw-dresden.de/', new EaseHtmlImgTag('img/nagstamon_header_logo.gif')));

$oPage->columnI->addItem(new EaseHtmlPTag(_('<br>
Nagstamon je monitor Nagiosu pro desktopové operační systémy.
Běží v systémové liště, nebo jako plovoucí aplikace, či ve stavovém řádku na
ploše a ukazuje stručný přehled stavy hostitelů či služeb tzn:
kritický, varovný, neznámý, nedosažitelný či vypnuto  a ukazuje podrobný přehled
stavu při pohybu ukazatele myši nad ním.
Připojení k zobrazeným hostitelům a službám je snadné z kontextové nabídky přes
SSH, RDP a VNC nebo jakýchkoli jiných definovaných akcí.
Uživatelé mohou být upozorněni zvukem.
Hostitelé a služby mohou být filtrovány podle kategorií a regulárních výrazů.
')));

$oPage->columnII->addItem(new EaseHtmlImgTag('img/nagstamon1.png'));

$oPage->columnI->addItem(new EaseHtmlATag('http://nagstamon.ifw-dresden.de/', _('Domovská stránka programu')));

$oPage->columnI->addItem(new EaseHtmlH2Tag(_('Nastavení')));

$oPage->columnII->addItem('<p></p>');
$oPage->columnII->addItem(new EaseHtmlImgTag('img/nagstamon2.png'));

$oPage->columnI->addItem('<p>Type: <b>icinga</b>');
$oPage->columnI->addItem('<p>Monitor URL: <b>http://v.s.cz/icinga/</b></p>');
$oPage->columnI->addItem('<p>Monitor Cgi URL: <b>http://v.s.cz/cgi-bin/icinga/</b></p>');
$oPage->columnI->addItem('<p>Username: <b>' . $oUser->getUserLogin() . '</b></p>');
$oPage->columnI->addItem('<p>Password: <b>vaše heslo</b></p>');

$oPage->columnI->addItem(new EaseTWBLinkButton('settings.php', _('Změna hesla'), 'danger'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
