<?php

/**
 * Icinga Editor - časové periody
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IETimeperiod.php';

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Přehled časových period')));


$Timeperiod = new IETimeperiod();
$Periods = $Timeperiod->getListing();

if ($Periods) {

    $CntList = new EaseHtmlTableTag(null,array('class'=>'table'));

    $Cid = 1;
    foreach ($Periods as $CID => $CInfo) {
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('timeperiod.php?timeperiod_id=' . $CInfo['timeperiod_id'], $CInfo['timeperiod_name'].' <i class="icon-edit"></i>')));
        if($CInfo['generate'] == 0){
            $LastRow->setTagCss(array('border-right'=>'1px solid red'));
        }
        if($CInfo['public'] == 1){
            if($CInfo[$Timeperiod->UserColumn] == $OUser->getUserID()){
                $LastRow->setTagCss(array('border-left'=>'1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left'=>'1px solid blue'));
            }
        }
    }
    $OPage->column2->addItem($CntList);
} else {
    $OUser->addStatusMessage(_('Nemáte definované časové periody'), 'warning');
}

$OPage->column3->addItem(new EaseTWBLinkButton('timeperiod.php', _('Založit časovou periodu <i class="icon-edit"></i>')));



$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
