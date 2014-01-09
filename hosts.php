<?php

/**
 * Icinga Editor - přehled hostů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHost.php';
require_once 'classes/IEHostOverview.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled hostů')));

$oPage->addCss('td img { height: 14px; }');


$host = new IEHost();
$hosts = $host->getListing(null,true,array('icon_image','platform'));

if ($hosts) {
    $oPage->columnII->addItem(new EaseHtmlH4Tag(_('Hosty')));
    $cntList = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($hosts as $cId => $cInfo) {
        if($cInfo['register'] != 1){
            continue;
        }
        $lastRow = $cntList->addRowColumns(array($Cid++, IEHostOverview::icon($cInfo), new EaseHtmlATag('host.php?host_id=' . $cInfo['host_id'], $cInfo['host_name'].' <i class="icon-edit"></i>'),  IEHostOverview::platformIcon($cInfo['platform'])));
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if($cInfo['public'] == 1){
            if($cInfo[$host->userColumn] == $oUser->getUserID()){
                $lastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $oPage->columnII->addItem($cntList);

    $oPage->columnI->addItem(new EaseHtmlH4Tag(_('Předlohy')));
    $Cnt2List = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($hosts as $cId => $cInfo) {
        if(intval($cInfo['register'])){
            continue;
        }
        $lastRow = $Cnt2List->addRowColumns(array($Cid++, IEHostOverview::icon($cInfo), new EaseHtmlATag('host.php?host_id=' . $cInfo['host_id'], $cInfo['name'].' '.EaseTWBPart::GlyphIcon('edit')),new EaseHtmlATag('host.php?use='. urldecode($cInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
       if($cInfo['public'] == 1){
            if($cInfo[$host->userColumn] == $oUser->getUserID()){
                $lastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $oPage->columnI->addItem($Cnt2List);
    
} else {
    $oUser->addStatusMessage(_('Nemáte definovaný žádný host'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('host.php', _('Založit host <i class="icon-edit"></i>')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
