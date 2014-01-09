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




$contact = new IEContact();
$Contacts = $contact->getListing();

if ($Contacts) {
    $oPage->columnII->addItem(new EaseHtmlH4Tag(_('Kontakty')));
    $cntList = new EaseHtmlTableTag(null,array('class'=>'table'));
    $Cid = 1;
    foreach ($Contacts as $cId => $cInfo) {
        $cInfo['public'] = false;
        if($cInfo['register'] != 1){
            continue;
        }
        $lastRow = $cntList->addRowColumns(array($Cid++, new EaseHtmlATag('contact.php?contact_id=' . $cInfo['contact_id'], $cInfo['contact_name'].' <i class="icon-edit"></i>')));
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if($cInfo['public'] == 1){
            if($cInfo[$contact->userColumn] == $oUser->getUserID()){
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
    foreach ($Contacts as $cId => $cInfo) {
        $cInfo['public'] = false;
        if(intval($cInfo['register'])){
            continue;
        }
        $lastRow = $Cnt2List->addRowColumns(array($Cid++, new EaseHtmlATag('contact.php?contact_id=' . $cInfo['contact_id'], $cInfo['name'].' <i class="icon-edit"></i>'),new EaseHtmlATag('contact.php?use='. urldecode($cInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
       if($cInfo['public'] == 1){
            if($cInfo[$contact->userColumn] == $oUser->getUserID()){
                $lastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $oPage->columnI->addItem($Cnt2List);
    
} else {
    $oUser->addStatusMessage(_('Nemáte definovaný žádný contact'), 'warning');
    $oPage->columnIII->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt <i class="icon-edit"></i>')));
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('contact.php', _('Založit kontakt <i class="icon-edit"></i>')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
