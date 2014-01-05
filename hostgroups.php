<?php

/**
 * Icinga Editor - přehled skupin hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHostgroup.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled skupin hostů')));

$Hostgroup = new IEHostgroup();
$PocContactgroup = $Hostgroup->getMyRecordsCount();

if ($PocContactgroup) {
    $Hostgroups = $Hostgroup->myDbLink->queryToArray('SELECT ' . $Hostgroup->getmyKeyColumn() . ', hostgroup_name, DatSave FROM ' . $Hostgroup->myTable . ' WHERE user_id=' . $oUser->getUserID(), 'hostgroup_id');
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Hostgroups as $CID => $CInfo) {
        $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('hostgroup.php?hostgroup_id=' . $CInfo['hostgroup_id'], $CInfo['hostgroup_name'].' <i class="icon-edit"></i>')));
    }
    $oPage->columnII->addItem($CntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu hostů'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('hostgroup.php', _('Založit skupinu hostů <i class="icon-edit"></i>')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
