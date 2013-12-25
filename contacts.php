<?php

/**
 * Icinga Editor - přehled contactů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContact.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled contactů')));




$Contact = new IEContact();
$Contacts = $Contact->getListing();

if ($Contacts) {
    $oPage->column2->addItem(new EaseHtmlH4Tag(_('Kontakty')));
    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($Contacts as $CID => $CInfo) {
        $CInfo['public'] = false;
        if($CInfo['register'] != 1){
            continue;
        }
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('contact.php?contact_id=' . $CInfo['contact_id'], $CInfo['contact_name'].' <i class="icon-edit"></i>')));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if($CInfo['public'] == 1){
            if($CInfo[$Contact->UserColumn] == $oUser->getUserID()){
                $LastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $oPage->column2->addItem($CntList);

    $oPage->column1->addItem(new EaseHtmlH4Tag(_('Předlohy')));
    $Cnt2List = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($Contacts as $CID => $CInfo) {
        $CInfo['public'] = false;
        if(intval($CInfo['register'])){
            continue;
        }
        $LastRow = $Cnt2List->addRowColumns(array($Cid++, new EaseHtmlATag('contact.php?contact_id=' . $CInfo['contact_id'], $CInfo['name'].' <i class="icon-edit"></i>'),new EaseHtmlATag('contact.php?use='. urldecode($CInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
       if($CInfo['public'] == 1){
            if($CInfo[$Contact->UserColumn] == $oUser->getUserID()){
                $LastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $oPage->column1->addItem($Cnt2List);
    
} else {
    $oUser->addStatusMessage(_('Nemáte definovaný žádný contact'), 'warning');
    $oPage->column3->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt <i class="icon-edit"></i>')));
}

$oPage->column3->addItem(new EaseTWBLinkButton('contact.php', _('Založit kontakt <i class="icon-edit"></i>')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
