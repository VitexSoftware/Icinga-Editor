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

if ($OUser->getUserID()) {
    $OUser->logout();
    $MessagesBackup = $OUser->getStatusMessages(TRUE);
    EaseShared::user(new EaseAnonym());
    $OUser->addStatusMessages($MessagesBackup);
}

$OPage->addItem(new IEPageTop(_('Odhlášení')));

$OPage->column2->addItem(new EaseHtmlDivTag(NULL, _('Děkujeme za vaši přízeň a těšíme se na další návštěvu')));

$OPage->addItem(new IEPageBottom());

$OPage->draw();
?>
