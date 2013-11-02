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

$OPage->onlyForLogged();

$OPage->addItem(new IEPageTop(_('Přehled služeb')));

$Service = new IEService();
$Services = $Service->getListing();

if ($Services) {
    $OPage->column1->addItem(new EaseHtmlH4Tag(_('Předlohy služeb')));
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
            if ($CInfo[$Service->UserColumn] == $OUser->getUserID()) {
                $LastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $OPage->column1->addItem($CntList);


    $OPage->column2->addItem(new EaseHtmlH4Tag(_('Služby')));
    $CntList = new EaseHtmlTableTag(null, array('class' => 'table'));
    $Cid = 1;
    foreach ($Services as $CID => $CInfo) {
        if($CInfo['register'] != 1){
            continue;
        }
        $LastRow = $CntList->addRowColumns(array($Cid++, new EaseHtmlATag('service.php?service_id=' . $CInfo['service_id'], $CInfo[$Service->NameColumn] . ' <i class="icon-edit"></i>')));
        if ($CInfo['generate'] == 0) {
            $LastRow->setTagCss(array('border-right' => '1px solid red'));
        }
        if ($CInfo['public'] == 1) {
            if ($CInfo[$Service->UserColumn] == $OUser->getUserID()) {
                $LastRow->setTagCss(array('border-left' => '1px solid green'));
            } else {
                $LastRow->setTagCss(array('border-left' => '1px solid blue'));
            }
        }
    }
    $OPage->column2->addItem($CntList);
} else {
    $OUser->addStatusMessage(_('Nemáte definovanou žádnou službu'), 'warning');
}

$OPage->column3->addItem(new EaseTWBLinkButton('service.php', _('Založit službu <i class="icon-edit"></i>')));



$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
