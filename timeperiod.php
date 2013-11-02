<?php

/**
 * Icinga Editor Timeprioda
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IETimeperiod.php';
require_once 'classes/IECfgEditor.php';

$OPage->onlyForLogged();

$Timeperiod = new IETimeperiod($OPage->getRequestValue('timeperiod_id', 'int'));

if ($OPage->isPosted()) {
    unset($_POST['Save']);
    $Timeperiod->takeData($_POST);
    $TimepriodID = $Timeperiod->saveToMySQL();
    if (is_null($TimepriodID)) {
        $OUser->addStatusMessage(_('časová perioda nebyl uložena'), 'warning');
    } else {
        $OUser->addStatusMessage(_('časová byla uložena'), 'success');
    }
}


$DelColumn = $OPage->getGetValue('del');
if (!is_null($DelColumn)) {
    $Del = $Timeperiod->delTime($DelColumn);
    $TimepriodID = $Timeperiod->saveToMySQL();
    if (is_null($TimepriodID) && !$Del) {
        $OUser->addStatusMessage(_('položka nebyla odebrána'), 'warning');
    } else {
        $OUser->addStatusMessage(_('položka byla odebrána'), 'success');
    }
}

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Timeperiod->delete();
}



$OPage->addItem(new IEPageTop(_('Editace časové periody') . ' ' . $Timeperiod->getName()));

$TimepriodEdit = new IECfgEditor($Timeperiod);


$Form = $OPage->column2->addItem(new EaseHtmlForm('Perioda', 'timeperiod.php', 'POST', $TimepriodEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Timeperiod->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Timeperiod->getMyKeyColumn(), $Timeperiod->getMyKey()));
}
$TimesTable = new EaseHtmlTableTag();

$TimesTable->addRowHeaderColumns(array(new EaseLabeledTextInput('NewKey', null, _('Označení')),
    new EaseLabeledTextInput('NewTimes', null, _('Interval(y)')), ''));

foreach ($Timeperiod->Timeperiods as $TimeName => $TimeIntervals) {
    $TimesTable->addRowColumns(array($TimeName, $TimeIntervals, new EaseHtmlATag('?del=' . $TimeName . '&amp;' . $Timeperiod->getMyKeyColumn() . '=' . $Timeperiod->getMyKey(), '<i class="icon-remove"></i>')));
}

$Form->addItem($TimesTable);

$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$OPage->AddCss('
input.ui-button { width: 100%; }
');


$OPage->column3->addItem($Timeperiod->deleteButton());

if ($Timeperiod->getId()) {
    $OPage->column1->addItem($Timeperiod->ownerLinkButton());
}


$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
