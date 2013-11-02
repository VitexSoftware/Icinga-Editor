<?php

/**
 * Icinga Editor - přehled skupin služeb
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEServicegroup.php';

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Přehled skupin služeb')));




$Servicegroup = new IEServicegroup();
$PocServicegroup = $Servicegroup->getMyRecordsCount();

if ($PocServicegroup) {
    $Servicegroups = $Servicegroup->MyDbLink->queryToArray('SELECT ' . $Servicegroup->getMyKeyColumn() . ', servicegroup_name, DatSave FROM ' . $Servicegroup->MyTable . ' WHERE user_id=' . $OUser->getUserID(), 'servicegroup_id');
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Servicegroups as $CID => $CInfo) {
        $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('servicegroup.php?servicegroup_id=' . $CInfo['servicegroup_id'], $CInfo['servicegroup_name'].' <i class="icon-edit"></i>')));
    }
    $OPage->column2->addItem($CntList);
} else {
    $OUser->addStatusMessage(_('Nemáte definovou skupinu služeb'), 'warning');
}

$OPage->column3->addItem(new EaseTWBLinkButton('servicegroup.php', _('Založit skupinu služeb <i class="icon-edit"></i>')));



$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
