<?php

/**
 * Icinga Editor - přehled služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEService.php';
require_once 'classes/IEHostOverview.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled služeb')));

$oPage->addCss('td img { height: 14px; }');

$service = new IEService();
$services = $service->getListing(null,true,array('platform'));

if ($services) {
    $oPage->columnI->addItem(new EaseHtmlH4Tag(_('Předlohy služeb')));
    $cntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($services as $servID => $servInfo) {
        if (intval($servInfo['register'])) {
            continue;
        }
        $lastRow = $cntList->addRowColumns(array($Cid++, new EaseHtmlATag('service.php?service_id=' . $servInfo['service_id'], $servInfo['name'] . ' <i class="icon-edit"></i>'), new EaseHtmlATag('service.php?use=' . urldecode($servInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($servInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($servInfo['public'] == 1) {
            if ($servInfo[$service->userColumn] == $oUser->getUserID()) {
                $lastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $oPage->columnI->addItem($cntList);

    $oPage->columnII->addItem(new EaseHtmlH4Tag(_('Služby')));
    $cntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($services as $servID => $servInfo) {
        if ($servInfo['register'] != 1) {
            continue;
        }
        $lastRow = $cntList->addRowColumns(array($Cid++, IEHostOverview::platformIcon($servInfo['platform'])  , new EaseHtmlATag('service.php?service_id=' . $servInfo['service_id'], $servInfo[$service->nameColumn] . ' <i class="icon-edit"></i>')));
        if ($servInfo['generate'] == 0) {
            $lastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($servInfo['public'] == 1) {
            if ($servInfo[$service->userColumn] == $oUser->getUserID()) {
                $lastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $lastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $oPage->columnII->addItem($cntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovanou žádnou službu'), 'warning');
}

$oPage->columnIII->addItem(new EaseTWBLinkButton('service.php', _('Založit službu <i class="icon-edit"></i>')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
