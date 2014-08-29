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

$hostgroup = new IEHostgroup();
$pocContactgroup = $hostgroup->getMyRecordsCount();

if ($pocContactgroup) {
    $hgList = $hostgroup->getListing(null,false);

    foreach ($hgList as $cId => $cInfo) {
        $cntList = new EaseHtmlDivTag('listing',null,array('class'=>'well'));
        $hostgroup = new IEHostgroup($cId);
        $cntList->addItem( new EaseHtmlH3Tag( new EaseHtmlATag('hostgroup.php?hostgroup_id=' . $cInfo['hostgroup_id'], EaseTWBPart::GlyphIcon('edit').' '.$hostgroup->getName()) ) );
        $cntList->addItem( new EaseHtmlDivTag( null, $hostgroup->getDataValue('alias') ) );
        foreach ( $hostgroup->getDataValue('members') as $memberID => $memberName ) {
            $cntList->addItem( new EaseHtmlATag('host.php?host_id='.$memberID,  new IEHost( $memberID ) ) );
        }
        $oPage->addItem($cntList);
    }
} else {
    $oUser->addStatusMessage(_('Nemáte definovou skupinu hostů'), 'warning');
}

//$oPage->addItem(new EaseTWBLinkButton('hostgroup.php', EaseTWBPart::GlyphIcon('plus').' '._('Založit skupinu hostů')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
