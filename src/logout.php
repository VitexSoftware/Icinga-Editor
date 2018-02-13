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
    \Ease\Shared::user(new \Ease\Anonym());
    $oUser->addStatusMessages($messagesBackup);
}

$oPage->addItem(new UI\PageTop(_('Odhlášení')));
$oPage->addPageColumns();

$oPage->heroUnit = $oPage->container->addItem(new \Ease\Html\Div(
        null, ['class' => 'jumbotron', 'id' => 'heroUnit']));

$oPage->heroUnit->addItem(new \Ease\Html\Div(_('Thank you for your favor and we are looking forward to your next visit')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
