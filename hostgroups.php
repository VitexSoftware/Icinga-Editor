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

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Přehled skupin hostů')));




$Hostgroup = new IEHostgroup();
$PocContactgroup = $Hostgroup->getMyRecordsCount();

if ($PocContactgroup) {
    $Hostgroups = $Hostgroup->MyDbLink->queryToArray('SELECT ' . $Hostgroup->getMyKeyColumn() . ', hostgroup_name, DatSave FROM ' . $Hostgroup->MyTable . ' WHERE user_id=' . $OUser->getUserID(), 'hostgroup_id');
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Hostgroups as $CID => $CInfo) {
        $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('hostgroup.php?hostgroup_id=' . $CInfo['hostgroup_id'], $CInfo['hostgroup_name'].' <i class="icon-edit"></i>')));
    }
    $OPage->column2->addItem($CntList);
} else {
    $OUser->addStatusMessage(_('Nemáte definovou skupinu hostů'), 'warning');
}

$OPage->column3->addItem(new EaseTWBLinkButton('hostgroup.php', _('Založit skupinu hostů <i class="icon-edit"></i>')));



$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
