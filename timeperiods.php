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

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled časových period')));
$oPage->addPageColumns();

$Timeperiod = new IETimeperiod();
$Periods = $Timeperiod->getListing();

if ($Periods) {

    $cntList = new EaseHtmlTableTag(null, array('class' => 'table'));

    $cid = 1;
    foreach ($Periods as $cId => $cInfo) {
        $lastRow = $cntList->addRowColumns(array($cid++, new EaseHtmlATag('timeperiod.php?timeperiod_id=' . $cInfo['timeperiod_id'], $cInfo['timeperiod_name'] . ' <i class="icon-edit"></i>')));
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($cInfo['public'] == 1) {
            if ($cInfo[$Timeperiod->userColumn] == $oUser->getUserID()) {
                $lastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definované časové periody'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('timeperiod.php', _('Založit časovou periodu ' . EaseTWBPart::GlyphIcon('edit'))));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
