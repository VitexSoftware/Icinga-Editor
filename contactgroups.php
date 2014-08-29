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
$pocContactgroup = $Contactgroup->getMyRecordsCount();

if ($pocContactgroup) {
    $Contactgroups = $Contactgroup->myDbLink->queryToArray('SELECT ' . $Contactgroup->getmyKeyColumn() . ', contactgroup_name, DatSave FROM ' . $Contactgroup->myTable . ' WHERE user_id=' . $oUser->getUserID(), 'contactgroup_id');
    $cntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $cid = 1;
    foreach ($Contactgroups as $cId => $cInfo) {
        $cntList->addRowColumns(array($cid++, new EaseHtmlATag('contactgroup.php?contactgroup_id=' . $cInfo['contactgroup_id'], $cInfo['contactgroup_name'].' <i class="icon-edit"></i>')));
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu kontaktů'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('contactgroup.php', _('Založit skupinu kontaktů <i class="icon-edit"></i>')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
