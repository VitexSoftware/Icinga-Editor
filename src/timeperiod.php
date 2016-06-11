<?php

namespace Icinga\Editor;

/**
 * Icinga Editor Timeprioda
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$Timeperiod = new Engine\IETimeperiod($oPage->getRequestValue('timeperiod_id', 'int'));

if ($oPage->isPosted()) {
    unset($_POST['Save']);
    $Timeperiod->takeData($_POST);
    $TimepriodID = $Timeperiod->saveToSQL();
    if (is_null($TimepriodID)) {
        $oUser->addStatusMessage(_('časová perioda nebyl uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('časová byla uložena'), 'success');
    }
}

$DelColumn = $oPage->getGetValue('del');
if (!is_null($DelColumn)) {
    $Del         = $Timeperiod->delTime($DelColumn);
    $TimepriodID = $Timeperiod->saveToSQL();
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

$oPage->addItem(new UI\PageTop(_('Editace časové periody').' '.$Timeperiod->getName()));
$oPage->addPageColumns();

$TimepriodEdit = new UI\CfgEditor($Timeperiod);

$form = $oPage->columnII->addItem(new \Ease\Html\Form('Perioda',
    'timeperiod.php', 'POST', $TimepriodEdit, ['class' => 'form-horizontal']));
$form->setTagID($form->getTagName());
if (!is_null($Timeperiod->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($Timeperiod->getmyKeyColumn(),
        $Timeperiod->getMyKey()));
}
$timesTable = new \Ease\Html\TableTag();

$timesTable->addRowHeaderColumns(
    [
        new \Ease\TWB\FormGroup(_('Označení'),
            new \Ease\Html\InputTextTag('NewKey'))
        ,
        new \Ease\TWB\FormGroup(_('Interval(y)'),
            new \Ease\Html\InputTextTag('NewTimes'))
]);

//$timesTable->addRowHeaderColumns([new EaseLabeledTextInput('NewKey', null,
//        _('Označení')),
//    new EaseLabeledTextInput('NewTimes', null, _('Interval(y)')), '']);

foreach ($Timeperiod->timeperiods as $timeName => $TimeIntervals) {
    $timesTable->addRowColumns([$timeName, $TimeIntervals, new \Ease\Html\ATag('?del='.$timeName.'&amp;'.$Timeperiod->getmyKeyColumn().'='.$Timeperiod->getMyKey(),
            '<i class="icon-remove"></i>')]);
}

$form->addItem($timesTable);

$form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($Timeperiod->deleteButton());

if ($Timeperiod->getId()) {
    $oPage->columnI->addItem($Timeperiod->ownerLinkButton());
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
