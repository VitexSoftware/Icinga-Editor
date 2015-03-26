<?php

/**
 * Icinga Editor - Předloha sledovaných služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$stemplate = new IEStemplate($oPage->getRequestValue('stemplate_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'new':
        $stemplate->setDataValue($stemplate->nameColumn, _('Nová předloha'));
        $stemplate->insertToSQL();
        $stemplate->setDataValue($stemplate->nameColumn, _('Nová předloha') . ' #' . $stemplate->getId());
        $stemplate->updateToMySQL();

        break;
    case 'copyhost':
        $host = new IEHost($oPage->getRequestValue('host_id', 'int'));

        $stemplate->setDataValue($stemplate->nameColumn, $host->getName());
        $stemplate->setDataValue('services', $host->getServices());
        if ($stemplate->saveToSQL()) {
            $stemplate->addStatusMessage(sprintf(_('Vytvořena nová předloha sledovaných služeb: %s'), $stemplate->getName()), 'success');
        } else {
            $stemplate->addStatusMessage(sprintf(_('Nebyla vytvořena nová předloha')), 'warning');
        }

        break;
    case 'export':
        $stemplate->transfer($oPage->getRequestValue('destination'));
        break;
    default :
        if ($oPage->isPosted()) {
            $stemplate->takeData($_POST);
            if (!$stemplate->getName()) {
                $oUser->addStatusMessage(_('Není zadán název'), 'warning');
            }
            $stemplateID = $stemplate->saveToMySQL();

            if (is_null($stemplateID)) {
                $oUser->addStatusMessage(_('Příkaz nebyl uložen'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Příkaz byl uložen'), 'success');
            }
        }
}



$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $stemplate->delete();
}

$oPage->addItem(new IEPageTop(_('Editace předvolby sledovaných služeb') . ' ' . $stemplate->getName()));
$oPage->addPageColumns();

if ($stemplate->getId()) {
    $oPage->columnIII->addItem($stemplate->deleteButton());
}

switch ($oPage->getRequestValue('action')) {
    case 'delete':

        $oPage->columnII->addItem(new EaseHtmlH2Tag($stemplate->getName()));

        $confirmator = $oPage->columnII->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new EaseTWBLinkButton('?' . $stemplate->myKeyColumn . '=' . $stemplate->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $stemplate->myKeyColumn . '=' . $stemplate->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));


        break;
    default :
        $stemplateEditor = new IECfgEditor($stemplate);

        $form = $oPage->columnII->addItem(new EaseHtmlForm('Stemplate', 'stemplate.php', 'POST', $stemplateEditor, array('class' => 'form-horizontal')));

        if (!$stemplate->getId()) {
            $form->addItem(new EaseTWSubmitButton(_('Založit'), 'success'));
        } else {
            $form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
        }
        $oPage->columnIII->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $stemplate->transferForm()));
        break;
}


$oPage->addItem(new IEPageBottom());

$oPage->draw();
