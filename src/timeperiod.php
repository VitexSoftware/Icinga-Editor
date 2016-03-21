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

$Timeperiod = new IETimeperiod($oPage->getRequestValue('timeperiod_id', 'int'));

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
    $Del = $Timeperiod->delTime($DelColumn);
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

$oPage->addItem(new UI\PageTop(_('Editace časové periody') . ' ' . $Timeperiod->getName()));
$oPage->addPageColumns();

$TimepriodEdit = new IECfgEditor($Timeperiod);

$form = $oPage->columnII->addItem(new \Ease\Html\Form('Perioda', 'timeperiod.php', 'POST', $TimepriodEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($Timeperiod->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($Timeperiod->getmyKeyColumn(), $Timeperiod->getMyKey()));
}
$TimesTable = new \Ease\Html\TableTag();

$TimesTable->addRowHeaderColumns(array(new EaseLabeledTextInput('NewKey', null, _('Označení')),
  new EaseLabeledTextInput('NewTimes', null, _('Interval(y)')), ''));

foreach ($Timeperiod->timeperiods as $TimeName => $TimeIntervals) {
    $TimesTable->addRowColumns(array($TimeName, $TimeIntervals, new \Ease\Html\ATag('?del=' . $TimeName . '&amp;' . $Timeperiod->getmyKeyColumn() . '=' . $Timeperiod->getMyKey(), '<i class="icon-remove"></i>')));
}

$form->addItem($TimesTable);

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
