<?php

/**
 * Icinga Editor - přehled userů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHost.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled uživatelů')));

$User = new EaseUser;

$Users = $User->getColumnsFromMySQL(array('id', 'login'), null, 'login', $oUser->getMyKeyColumn());

if ($Users) {
    $oPage->column2->addItem(new EaseHtmlH4Tag(_('Uživatelé')));
    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($Users as $CID => $CInfo) {
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseUser((int) $CID),
            new EaseHtmlATag('userinfo.php?user_id=' . $CID, $CInfo['login'] . ' <i class="icon-edit"></i>'),
            new EaseHtmlATag('apply.php?force_user_id=' . $CID, _('Přegenerovat konfiguraci') . ' <i class="icon-repeat"></i>')
                )
        );
    }
    $oPage->column2->addItem($CntList);
}

$oPage->column3->addItem(new EaseTWBLinkButton('createaccount.php', _('Založit uživatele <i class="icon-edit"></i>')));

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
