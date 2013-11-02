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

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Přehled hostů')));




$Host = new IEHost();
$Hosts = $Host->getListing();

if ($Hosts) {
    $OPage->column2->addItem(new EaseHtmlH4Tag(_('Hosty')));
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($Hosts as $CID => $CInfo) {
        if($CInfo['register'] != 1){
            continue;
        }
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('host.php?host_id=' . $CInfo['host_id'], $CInfo['host_name'].' <i class="icon-edit"></i>')));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if($CInfo['public'] == 1){
            if($CInfo[$Host->UserColumn] == $OUser->getUserID()){
                $LastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $OPage->column2->addItem($CntList);

    $OPage->column1->addItem(new EaseHtmlH4Tag(_('Předlohy')));
    $Cnt2List = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($Hosts as $CID => $CInfo) {
        if(intval($CInfo['register'])){
            continue;
        }
        $LastRow = $Cnt2List->addRowColumns(array($Cid++, new EaseHtmlATag('host.php?host_id=' . $CInfo['host_id'], $CInfo['name'].' <i class="icon-edit"></i>'),new EaseHtmlATag('host.php?use='. urldecode($CInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
       if($CInfo['public'] == 1){
            if($CInfo[$Host->UserColumn] == $OUser->getUserID()){
                $LastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $OPage->column1->addItem($Cnt2List);
    
} else {
    $OUser->addStatusMessage(_('Nemáte definovaný žádný host'), 'warning');
}

$OPage->column3->addItem(new EaseTWBLinkButton('host.php', _('Založit host <i class="icon-edit"></i>')));



$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
