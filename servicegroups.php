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




$serviceGroup = new IEServicegroup();
$PocServicegroup = $serviceGroup->getMyRecordsCount();

if ($PocServicegroup) {
    $Servicegroups = $serviceGroup->myDbLink->queryToArray('SELECT ' . $serviceGroup->getmyKeyColumn() . ', servicegroup_name, DatSave FROM ' . $serviceGroup->myTable . ' WHERE user_id=' . $oUser->getUserID(), 'servicegroup_id');
    $cntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $cid = 1;
    foreach ($Servicegroups as $cId => $cInfo) {
        $cntList->addRowColumns(array($cid++, new EaseHtmlATag('servicegroup.php?servicegroup_id=' . $cInfo['servicegroup_id'], $cInfo['servicegroup_name'].' <i class="icon-edit"></i>')));
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu služeb'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('servicegroup.php', _('Založit skupinu služeb').' '.EaseTWBPart::GlyphIcon('edit')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
