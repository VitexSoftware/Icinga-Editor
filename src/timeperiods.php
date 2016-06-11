<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - časové periody
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Přehled časových period')));
$oPage->addPageColumns();

$Timeperiod = new Engine\IETimeperiod();
$Periods    = $Timeperiod->getListing();

if ($Periods) {

    $cntList = new \Ease\Html\TableTag(null, ['class' => 'table']);

    $cid = 1;
    foreach ($Periods as $cId => $cInfo) {
        $lastRow = $cntList->addRowColumns([$cid++, new \Ease\Html\ATag('timeperiod.php?timeperiod_id='.$cInfo['timeperiod_id'],
                $cInfo['timeperiod_name'].' <i class="icon-edit"></i>')]);
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(['border-right' => '1px solid red']);
        }
        if ($cInfo['public'] == 1) {
            if ($cInfo[$Timeperiod->userColumn] == $oUser->getUserID()) {
                $lastRow->setTagCss(['border-left' => '1px solid green']);
            } else {
                $lastRow->setTagCss(['border-left' => '1px solid blue']);
            }
        }
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definované časové periody'), 'warning');
}

$oPage->columnIII->addItem(new \Ease\TWB\LinkButton('timeperiod.php',
    _('Založit časovou periodu '.\Ease\TWB\Part::GlyphIcon('edit'))));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
