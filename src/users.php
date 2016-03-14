<?php
namespace Icinga\Editor;

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

$oPage->addItem(new UI\PageTop(_('Přehled uživatelů')));
$oPage->addPageColumns();

$user = new IEUser;

$users = $user->getColumnsFromMySQL(array('id', 'login'), null, 'login', $oUser->getmyKeyColumn());

if ($users) {
    $oPage->columnII->addItem(new \Ease\Html\H4Tag(_('Uživatelé')));
    $cntList = new \Ease\Html\TableTag(null, array('class' => 'table'));
    $cid = 1;
    foreach ($users as $cId => $cInfo) {
        if (!$cId) {
            continue;
        }
        $lastRow = $cntList->addRowColumns(array($cid++, new IEUser((int) $cId),
          new \Ease\Html\ATag('userinfo.php?user_id=' . $cId, $cInfo['login'] . ' <i class="icon-edit"></i>'),
          new \Ease\Html\ATag('apply.php?force_user_id=' . $cId, _('Přegenerovat konfiguraci') . ' <i class="icon-repeat"></i>')
            )
        );
    }
    $oPage->columnII->addItem($cntList);
}

$oPage->columnIII->addItem(new \Ease\TWB\LinkButton('createaccount.php', _('Založit uživatele') . ' ' . \Ease\TWB\Part::GlyphIcon('edit')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
