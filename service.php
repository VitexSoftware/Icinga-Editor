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
        $service->transferDeps($oPage->getRequestValue('destination'), $oPage->getRequestValue('rels'));
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

$infopanel = new IEInfoBox($service);
$tools = new EaseTWBPanel(_('Nástroje'), 'warning');
$pageRow = new EaseTWBRow;
$pageRow->addColumn(2, $infopanel);
$mainPanel = $pageRow->addColumn(6);
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);

switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $confirmator = $mainPanel->addItem(new EaseTWBPanel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new EaseTWBLinkButton('?' . $service->myKeyColumn . '=' . $service->getID(), _('Ne') . ' ' . EaseTWBPart::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new EaseTWBLinkButton('?delete=true&' . $service->myKeyColumn . '=' . $service->getID(), _('Ano') . ' ' . EaseTWBPart::glyphIcon('remove'), 'danger'));
        $tools->addItem(new EaseTWBPanel(_('Výměna služby'), 'info', new IEServiceSwapForm($service)));
        $infopanel->addItem($service->ownerLinkButton());
        $tools->addItem(new IEHostSelector($service));

        break;
    default :

        $serviceEdit = new IECfgEditor($service);

        $form = new EaseTWBForm('Service', 'service.php', 'POST', $serviceEdit, array('class' => 'form-horizontal'));
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
            $tools->addItem($service->deleteButton());
            $tools->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $service->transferForm()));
            $tools->addItem($service->cloneButton());


            $renameForm = new EaseTWBForm('Rename', '?action=rename&service_id=' . $service->getId());
            $renameForm->addItem(new EaseHtmlInputTextTag('newname'), $service->getName(), array('class' => 'form-control'));
            $renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

            $tools->addItem(new EaseTWBPanel(_('Přejmenování'), 'info', $renameForm));
            $tools->addItem(new IEHostSelector($service));

            if ($oUser->getSettingValue('admin')) {
                $tools->addItem(new EaseTWBLinkButton('?action=system&service_id=' . $service->getId(), _('Systémová služba')));
            }

            $tools->addItem(new EaseTWBPanel(_('Výměna služby'), 'info', new IEServiceSwapForm($service)));
            $tools->addItem(new EaseTWBPanel(_('Transfer'), 'warning', $service->transferForm()));
        }

        $mainPanel->addItem(new EaseTWBPanel(new EaseHtmlH3Tag(array(new IEPlatformIcon($service->getDataValue('platform')), $service->getName())), 'default', $form));

        break;
}



$oPage->addItem(new IEPageBottom());
$oPage->draw();
