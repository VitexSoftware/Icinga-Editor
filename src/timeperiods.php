<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - časové periody
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Timeperiod overview')));
$oPage->addPageColumns();

$timeperiod = new Engine\Timeperiod();
$periods = $timeperiod->getListing();

if ($periods) {

    $cntList = new \Ease\Html\TableTag(null, ['class' => 'table']);

    $cid = 1;
    foreach ($periods as $cId => $cInfo) {
        $lastRow = $cntList->addRowColumns([$cid++, new \Ease\Html\ATag('timeperiod.php?timeperiod_id=' . $cInfo['timeperiod_id'],
                    $cInfo['timeperiod_name'] . ' <i class="icon-edit"></i>')]);
        if ($cInfo['generate'] == 0) {
            $lastRow->setTagCss(['border-right' => '1px solid red']);
        }
        if ($cInfo['public'] == 1) {
            if ($cInfo[$timeperiod->userColumn] == $oUser->getUserID()) {
                $lastRow->setTagCss(['border-left' => '1px solid green']);
            } else {
                $lastRow->setTagCss(['border-left' => '1px solid blue']);
            }
        }
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('No timeperiods defined'), 'warning');
}

$oPage->columnIII->addItem(new \Ease\TWB\LinkButton('timeperiod.php',
                _('Create timeperiod') . ' ' . \Ease\TWB\Part::GlyphIcon('edit')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
