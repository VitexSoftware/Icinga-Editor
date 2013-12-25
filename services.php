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

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled služeb')));

$service = new IEService();
$Services = $service->getListing();

if ($Services) {
    $oPage->column1->addItem(new EaseHtmlH4Tag(_('Předlohy služeb')));
    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($Services as $CID => $CInfo) {
        if (intval($CInfo['register'])) {
            continue;
        }
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('service.php?service_id=' . $CInfo['service_id'], $CInfo['name'] . ' <i class="icon-edit"></i>'), new EaseHtmlATag('service.php?use=' . urldecode($CInfo['name']), _('Odvodit <i class="icon-edit"></i>'))));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($CInfo['public'] == 1) {
            if ($CInfo[$service->UserColumn] == $oUser->getUserID()) {
                $LastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $oPage->column1->addItem($CntList);


    $oPage->column2->addItem(new EaseHtmlH4Tag(_('Služby')));
    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($Services as $CID => $CInfo) {
        if($CInfo['register'] != 1){
            continue;
        }
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('service.php?service_id=' . $CInfo['service_id'], $CInfo[$service->NameColumn] . ' <i class="icon-edit"></i>')));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($CInfo['public'] == 1) {
            if ($CInfo[$service->UserColumn] == $oUser->getUserID()) {
                $LastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $oPage->column2->addItem($CntList);
} else {
    $oUser->addStatusMessage(_('Nemáte definovanou žádnou službu'), 'warning');
}

$oPage->column3->addItem(new EaseTWBLinkButton('service.php', _('Založit službu <i class="icon-edit"></i>')));



$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
