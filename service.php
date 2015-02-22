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
require_once 'classes/IEServiceSwapForm.php';

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
    case 'system':
        $hosts = $service->getDataValue('host_name');
        foreach ($hosts as $host_id => $host_name) {
            $host = new IEHost($host_id);
            $newService = new IEService($service->getId());
            $newService->setDataValue($service->userColumn, 0);
            $newService->setDataValue('public', 0);
            if ($newService->fork($host, $host->getDataValue($host->userColumn))) {
                $oUser->addStatusMessage(sprintf(_('Služba %s byla odvozena'), $newService->getName()), 'success');
            } else {
                $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
            }
        }
        $service->loadFromMySQL($service->getId());
        break;
    case 'swap':
        $service->swapTo($oPage->getRequestValue('new_service_id', 'int'));
        break;
    case 'export':
        $service->transfer($oPage->getRequestValue('destination'));
        break;
    default :
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
}

$service->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $service->delete();
}

$oPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $service->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag(array(IEHostOverview::platformIcon($service->getDataValue('platform')), $service->getName())));


switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $confirmator = $oPage->columnII->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new EaseTWBLinkButton('?' . $service->myKeyColumn . '=' . $service->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $service->myKeyColumn . '=' . $service->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));
        $oPage->columnIII->addItem(new EaseTWBPanel(_('Výměna služby'), 'info', new IEServiceSwapForm($service)));
        $oPage->columnI->addItem($service->ownerLinkButton());
        $oPage->columnII->addItem(new IEHostSelector($service));

        break;
    default :

        $serviceEdit = new IECfgEditor($service);


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

        if ($service->getID()) {
            $oPage->columnIII->addItem($service->deleteButton());
            $oPage->columnIII->addItem($service->cloneButton());

            $oPage->columnI->addItem($service->ownerLinkButton());
            $renameForm = new EaseTWBForm('Rename', '?action=rename&service_id=' . $service->getId());
            $renameForm->addItem(new EaseHtmlInputTextTag('newname'), $service->getName(), array('class' => 'form-control'));
            $renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

            $oPage->columnIII->addItem(new EaseTWBPanel(_('Přejmenování'), 'info', $renameForm));
            $oPage->columnI->addItem(new IEHostSelector($service));

            if ($oUser->getSettingValue('admin')) {
                $oPage->columnI->addItem(new EaseTWBLinkButton('?action=system&service_id=' . $service->getId(), _('Systémová služba')));
            }



            $oPage->columnIII->addItem(new EaseTWBPanel(_('Výměna služby'), 'info', new IEServiceSwapForm($service)));

            $oPage->columnIII->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $service->transferForm()));
        }
        break;
}


$oPage->addItem(new IEPageBottom());

$oPage->draw();
