<?php

namespace Icinga\Editor;

/**
 * Sign Off
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 */
require_once 'includes/IEInit.php';

unset($_SESSION['access_token']); //Twitter OAuth

if ($oUser->getUserID()) {
    $oUser->logout();
    $messagesBackup = $oUser->getStatusMessages(TRUE);
    $oUser->addStatusMessages($messagesBackup);
}

$oPage->addItem(new UI\PageTop(_('Odhlášení')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
