<?php

/**
 * Icinga Editor - přehled kontaktů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContactgroup.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled kontaktů')));




$Contactgroup = new IEContactgroup();
$PocContactgroup = $Contactgroup->getMyRecordsCount();

if ($PocContactgroup) {
    $Contactgroups = $Contactgroup->MyDbLink->queryToArray('SELECT ' . $Contactgroup->getMyKeyColumn() . ', contactgroup_name, DatSave FROM ' . $Contactgroup->MyTable . ' WHERE user_id=' . $oUser->getUserID(), 'contactgroup_id');
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Contactgroups as $CID => $CInfo) {
        $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('contactgroup.php?contactgroup_id=' . $CInfo['contactgroup_id'], $CInfo['contactgroup_name'].' <i class="icon-edit"></i>')));
    }
    $oPage->column2->addItem($CntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu kontaktů'), 'warning');
}

$oPage->column3->addItem(new EaseTWBLinkButton('contactgroup.php', _('Založit skupinu kontaktů <i class="icon-edit"></i>')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
