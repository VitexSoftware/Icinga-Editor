<?php

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
    $MessagesBackup = $oUser->getStatusMessages(TRUE);
    EaseShared::user(new EaseAnonym());
    $oUser->addStatusMessages($MessagesBackup);
}

$oPage->addItem(new IEPageTop(_('Odhlášení')));

$oPage->column2->addItem(new EaseHtmlDivTag(NULL, _('Děkujeme za vaši přízeň a těšíme se na další návštěvu')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
?>
