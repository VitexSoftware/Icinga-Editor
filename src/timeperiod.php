<?php

namespace Icinga\Editor;

/**
 * Icinga Timepriod Editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$timeperiod = new Engine\Timeperiod($oPage->getRequestValue('timeperiod_id',
        'int'));

if ($oPage->isPosted()) {
    unset($_POST['Save']);
    $timeperiod->takeData($_POST);
    $TimepriodID = $timeperiod->saveToSQL();
    if (is_null($TimepriodID)) {
        $oUser->addStatusMessage(_('timeperiod saving failed'), 'warning');
    } else {
        $oUser->addStatusMessage(_('timeperiod saved'), 'success');
    }
}

$delColumn = $oPage->getGetValue('del');
if (!is_null($delColumn)) {
    $Del         = $timeperiod->delTime($delColumn);
    $TimepriodID = $timeperiod->saveToSQL();
    if (is_null($TimepriodID) && !$Del) {
        $oUser->addStatusMessage(_('item was not removed'), 'warning');
    } else {
        $oUser->addStatusMessage(_('item was removed'), 'success');
    }
}

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $timeperiod->delete();
    $oPage->redirect('timeperiods.php');
}

$oPage->addItem(new UI\PageTop(_('Timeperiod editor').' '.$timeperiod->getName()));
$oPage->addPageColumns();

$TimepriodEdit = new UI\CfgEditor($timeperiod);

$form = $oPage->columnII->addItem(new \Ease\Html\Form('Period',
    'timeperiod.php', 'POST', $TimepriodEdit, ['class' => 'form-horizontal']));
$form->setTagID($form->getTagName());
if (!is_null($timeperiod->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($timeperiod->getmyKeyColumn(),
        $timeperiod->getMyKey()));
}
$timesTable = new \Ease\Html\TableTag();

$timesTable->addRowHeaderColumns(
    [
        new \Ease\TWB\FormGroup(_('Label'),
            new \Ease\Html\InputTextTag('NewKey'))
        ,
        new \Ease\TWB\FormGroup(_('Interval(s)'),
            new \Ease\Html\InputTextTag('NewTimes'))
]);

foreach ($timeperiod->timeperiods as $timeName => $TimeIntervals) {
    $timesTable->addRowColumns([$timeName, $TimeIntervals, new \Ease\Html\ATag('?del='.$timeName.'&amp;'.$timeperiod->getmyKeyColumn().'='.$timeperiod->getMyKey(),
            '<i class="icon-remove"></i>')]);
}

$form->addItem($timesTable);

$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($timeperiod->deleteButton());

if ($timeperiod->getId()) {
    $oPage->columnI->addItem($timeperiod->ownerLinkButton());
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
