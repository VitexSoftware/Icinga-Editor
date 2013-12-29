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

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled skupin služeb')));




$Servicegroup = new IEServicegroup();
$PocServicegroup = $Servicegroup->getMyRecordsCount();

if ($PocServicegroup) {
    $Servicegroups = $Servicegroup->myDbLink->queryToArray('SELECT ' . $Servicegroup->getmyKeyColumn() . ', servicegroup_name, DatSave FROM ' . $Servicegroup->myTable . ' WHERE user_id=' . $oUser->getUserID(), 'servicegroup_id');
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Servicegroups as $CID => $CInfo) {
        $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('servicegroup.php?servicegroup_id=' . $CInfo['servicegroup_id'], $CInfo['servicegroup_name'].' <i class="icon-edit"></i>')));
    }
    $oPage->columnII->addItem($CntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu služeb'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('servicegroup.php', _('Založit skupinu služeb').' '.EaseTWBPart::GlyphIcon('edit')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
