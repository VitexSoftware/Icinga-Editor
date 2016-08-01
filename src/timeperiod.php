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

$timeperiod = new Engine\Timeperiod($oPage->getRequestValue('timeperiod_id',
        'int'));

if ($oPage->isPosted()) {
    unset($_POST['Save']);
    $timeperiod->takeData($_POST);
    $TimepriodID = $timeperiod->saveToSQL();
    if (is_null($TimepriodID)) {
        $oUser->addStatusMessage(_('časová perioda nebyl uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('časová byla uložena'), 'success');
    }
}

$DelColumn = $oPage->getGetValue('del');
if (!is_null($DelColumn)) {
    $Del         = $timeperiod->delTime($DelColumn);
    $TimepriodID = $timeperiod->saveToSQL();
    if (is_null($TimepriodID) && !$Del) {
        $oUser->addStatusMessage(_('položka nebyla odebrána'), 'warning');
    } else {
        $oUser->addStatusMessage(_('položka byla odebrána'), 'success');
    }
}

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $timeperiod->delete();
    $oPage->redirect('timeperiods.php');
}

$oPage->addItem(new UI\PageTop(_('Editace časové periody').' '.$timeperiod->getName()));
$oPage->addPageColumns();

$TimepriodEdit = new UI\CfgEditor($timeperiod);

$form = $oPage->columnII->addItem(new \Ease\Html\Form('Perioda',
    'timeperiod.php', 'POST', $TimepriodEdit, ['class' => 'form-horizontal']));
$form->setTagID($form->getTagName());
if (!is_null($timeperiod->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($timeperiod->getmyKeyColumn(),
        $timeperiod->getMyKey()));
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

foreach ($timeperiod->timeperiods as $timeName => $TimeIntervals) {
    $timesTable->addRowColumns([$timeName, $TimeIntervals, new \Ease\Html\ATag('?del='.$timeName.'&amp;'.$timeperiod->getmyKeyColumn().'='.$timeperiod->getMyKey(),
            '<i class="icon-remove"></i>')]);
}

$form->addItem($timesTable);

$form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($timeperiod->deleteButton());

if ($timeperiod->getId()) {
    $oPage->columnI->addItem($timeperiod->ownerLinkButton());
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
