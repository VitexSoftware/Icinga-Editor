<?php

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEService.php';
require_once 'classes/IECfgEditor.php';
require_once 'classes/IEHostOverview.php';
require_once 'classes/IEHostSelector.php';
$oPage->onlyForLogged();

$service = new IEService($oPage->getRequestValue('service_id', 'int'));

switch ($oPage->getRequestValue('action')) {
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($service->rename($newname)) {
                $oUser->addStatusMessage(_('Host byl přejmenován'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Host nebyl přejmenován'), 'success');
            }
        }
        break;
    case 'clone':
        $service->unsetDataValue($service->getmyKeyColumn());
        $service->setDataValue($service->nameColumn, $service->getName() . ' ' . _('Cloned'));
        if ($service->saveToMySQL()) {
            $oUser->addStatusMessage(_('Služba byla zklonovana'), 'success');
        } else {
            $oUser->addStatusMessage(_('Služba nebyla zklonovana'), 'error');
        }

        break;
}

if ($oPage->isPosted()) {
    if ($oPage->getRequestValue('action') != 'rename') {
        if ($oPage->getRequestValue('action') == 'clone') {
            $oUser->addStatusMessage(_('Služba byla zklonovana'), 'info');
            $service->unsetDataValue($service->getMyKey());
        } else {
            $service->takeData($_POST);
        }
        $serviceID = $service->saveToMySQL();
        if (is_null($serviceID)) {
            $oUser->addStatusMessage(_('Služba nebyla uložena'), 'warning');
        } else {
            $oUser->addStatusMessage(_('Služba byla uložena'), 'success');
        }
    }
} else {
    $use = $oPage->getGetValue('use');
    if ($use) {
        $service->setDataValue('use', $use);
    }
}

$service->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $service->delete();
}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(IEHostOverview::platformIcon($service->getDataValue('platform')), $service->getName())));

$serviceEdit = new IECfgEditor($service);

$oPage->columnIII->addItem($service->deleteButton());
$oPage->columnIII->addItem($service->cloneButton());

if ($service->getId()) {
    $oPage->columnI->addItem($service->ownerLinkButton());
}

$form = $oPage->columnII->addItem(new EaseHtmlForm('Service', 'service.php', 'POST', $serviceEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($service->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($service->getMyKeyColumn(), $service->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));
$oPage->AddCss('
input.ui-button { width: 100%; }
');

$renameForm = new EaseTWBForm('Rename', '?action=rename&service_id=' . $service->getId());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $service->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new EaseHtmlFieldSet(_('Přejmenování'), $renameForm));
$oPage->columnI->addItem(new IEHostSelector($service));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
