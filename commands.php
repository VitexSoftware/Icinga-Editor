<?php

/**
 * Icinga Editor - přehled příkazů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IECommand.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled příkazů')));




$Command = new IECommand();
$Commands = $Command->getListing(null,null,array('command_local','command_remote','public'));



if ($Commands) {
    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));

    $Cid = 1;
    foreach ($Commands as $CID => $CInfo) {
        if (intval($CInfo['command_local'])) {
            $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('command.php?command_id=' . $CInfo['command_id'], $CInfo['command_name'])));
            if ($CInfo['generate'] == 0) {
                $LastRow->setTagCss(array('border-right' => '1px solid red'));
            }
            if ($CInfo['public'] == 1) {
                if ($CInfo[$Command->UserColumn] == $oUser->getUserID()) {
                    $LastRow->setTagCss(array('border-left' => '1px solid green'));
                } else {
                    $LastRow->setTagCss(array('border-left' => '1px solid blue'));
                }
            }
            unset($Commands[$CID]);
        }
    }
    $oPage->column1->addItem(new EaseHtmlH4Tag(_('Místní příkazy')));
    $oPage->column1->addItem($CntList);

    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($Commands as $CID => $CInfo) {
        if (intval($CInfo['command_remote'])) {
            $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('command.php?command_id=' . $CInfo['command_id'], $CInfo['command_name'])));
            if ($CInfo['generate'] == 0) {
                $LastRow->setTagCss(array('border-right' => '1px solid red'));
            }
            if ($CInfo['public'] == 1) {
                if ($CInfo[$Command->UserColumn] == $oUser->getUserID()) {
                    $LastRow->setTagCss(array('border-left' => '1px solid green'));
                } else {
                    $LastRow->setTagCss(array('border-left' => '1px solid blue'));
                }
            }
            unset($Commands[$CID]);
        }
        
    }
    $oPage->column2->addItem(new EaseHtmlH4Tag(_('vzdálené příkazy')));
    $oPage->column2->addItem($CntList);

    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    foreach ($Commands as $CID => $CInfo) {
            $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('command.php?command_id=' . $CInfo['command_id'], $CInfo['command_name'])));
            if ($CInfo['generate'] == 0) {
                $LastRow->setTagCss(array('border-right' => '1px solid red'));
            }
            if ($CInfo['public'] == 1) {
                if ($CInfo[$Command->UserColumn] == $oUser->getUserID()) {
                    $LastRow->setTagCss(array('border-left' => '1px solid green'));
                } else {
                    $LastRow->setTagCss(array('border-left' => '1px solid blue'));
                }
            }
            unset($Commands[$CID]);
    }
    $oPage->column3->addItem(new EaseHtmlH4Tag(_('neurčené příkazy')));
    $oPage->column3->addItem($CntList);
    
    
    
} else {
    $oUser->addStatusMessage(_('Nemáte definovaný příkaz'), 'warning');
}

$oPage->column3->addItem(new EaseTWBLinkButton('command.php', _('Založit příkaz') . ' <i class="icon-edit"></i>'));
$oPage->column3->addItem(new EaseTWBLinkButton('importcommand.php', _('Importovat příkazy') . ' <i class="icon-download"></i>'));


$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
