<?php

namespace Icinga\Editor;

/**
 * Odhlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

unset($_SESSION['access_token']); //Twitter OAuth

if ($oUser->getUserID()) {
    $oUser->logout();
    $messagesBackup = $oUser->getStatusMessages(TRUE);
    \Ease\Shared::user(new \Ease\Anonym());
    $oUser->addStatusMessages($messagesBackup);
}

$oPage->addItem(new UI\PageTop(_('Odhlášení')));
$oPage->addPageColumns();

$oPage->heroUnit = $oPage->container->addItem(new \Ease\Html\Div(
    null, ['class' => 'jumbotron', 'id' => 'heroUnit']));

$oPage->heroUnit->addItem(new \Ease\Html\Div(_('Děkujeme za vaši přízeň a těšíme se na další návštěvu')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
