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

$oPage->onlyForLogged();

$Timeperiod = new IETimeperiod($oPage->getRequestValue('timeperiod_id', 'int'));

if ($oPage->isPosted()) {
    unset($_POST['Save']);
    $Timeperiod->takeData($_POST);
    $TimepriodID = $Timeperiod->saveToMySQL();
    if (is_null($TimepriodID)) {
        $oUser->addStatusMessage(_('časová perioda nebyl uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('časová byla uložena'), 'success');
    }
}


$DelColumn = $oPage->getGetValue('del');
if (!is_null($DelColumn)) {
    $Del = $Timeperiod->delTime($DelColumn);
    $TimepriodID = $Timeperiod->saveToMySQL();
    if (is_null($TimepriodID) && !$Del) {
        $oUser->addStatusMessage(_('položka nebyla odebrána'), 'warning');
    } else {
        $oUser->addStatusMessage(_('položka byla odebrána'), 'success');
    }
}

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $Timeperiod->delete();
}



$oPage->addItem(new IEPageTop(_('Editace časové periody') . ' ' . $Timeperiod->getName()));

$TimepriodEdit = new IECfgEditor($Timeperiod);


$form = $oPage->column2->addItem(new EaseHtmlForm('Perioda', 'timeperiod.php', 'POST', $TimepriodEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Timeperiod->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($Timeperiod->getMyKeyColumn(), $Timeperiod->getMyKey()));
}
$TimesTable = new EaseHtmlTableTag();

$TimesTable->addRowHeaderColumns(array(new EaseLabeledTextInput('NewKey', null, _('Označení')),
    new EaseLabeledTextInput('NewTimes', null, _('Interval(y)')), ''));

foreach ($Timeperiod->Timeperiods as $TimeName => $TimeIntervals) {
    $TimesTable->addRowColumns(array($TimeName, $TimeIntervals, new EaseHtmlATag('?del=' . $TimeName . '&amp;' . $Timeperiod->getMyKeyColumn() . '=' . $Timeperiod->getMyKey(), '<i class="icon-remove"></i>')));
}

$form->addItem($TimesTable);

$form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');


$oPage->column3->addItem($Timeperiod->deleteButton());

if ($Timeperiod->getId()) {
    $oPage->column1->addItem($Timeperiod->ownerLinkButton());
}


$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
