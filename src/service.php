<?php

namespace Icinga\Editor;

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$service = new Engine\Service($oPage->getRequestValue('service_id', 'int'));

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
        $service->setDataValue($service->nameColumn,
            $service->getName().' '._('Cloned'));
        if ($service->saveToSQL()) {
            $oUser->addStatusMessage(_('Služba byla zklonovana'), 'success');
        } else {
            $oUser->addStatusMessage(_('Služba nebyla zklonovana'), 'error');
        }

        break;
    case 'system':
        $hosts = $service->getDataValue('host_name');
        foreach ($hosts as $host_id => $host_name) {
            $host       = new Engine\Host($host_id);
            $newService = new Engine\Service($service->getId());
            $newService->setDataValue($service->userColumn, 0);
            $newService->setDataValue('public', 0);
            if ($newService->fork($host, $host->getDataValue($host->userColumn))) {
                $oUser->addStatusMessage(sprintf(_('Služba %s byla odvozena'),
                        $newService->getName()), 'success');
            } else {
                $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
            }
        }
        $service->loadFromSQL($service->getId());
        break;
    case 'swap':
        $service->swapTo($oPage->getRequestValue('new_service_id', 'int'));
        break;
    case 'export':
        $service->transferDeps($oPage->getRequestValue('destination'),
            $oPage->getRequestValue('rels'));
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
                $serviceID = $service->saveToSQL();
                if (is_null($serviceID)) {
                    $oUser->addStatusMessage(_('Služba nebyla uložena'),
                        'warning');
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

$delete = $oPage->getGetValue('delete');
if ($delete === 'true') {
    $service->delete();
}

$oPage->addItem(new UI\PageTop(_('Editace služby').' '.$service->getName()));

$infopanel = new UI\InfoBox($service);
$tools     = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
$pageRow   = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$mainPanel = $pageRow->addColumn(6);
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);

switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $confirmator = $mainPanel->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')),
            'danger');
        $confirmator->addItem(new UI\RecordShow($service));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?'.$service->myKeyColumn.'='.$service->getID(),
            _('Ne').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$service->myKeyColumn.'='.$service->getID(),
            _('Ano').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));
        $tools->addItem(new \Ease\TWB\Panel(_('Výměna služby'), 'info',
            new UI\ServiceSwapForm($service)));
        $infopanel->addItem($service->ownerLinkButton());
        $tools->addItem(new UI\HostSelector($service));

        break;
    default :

        $serviceEdit = new UI\CfgEditor($service);

        $form = new \Ease\TWB\Form('Service', 'service.php', 'POST',
            $serviceEdit, ['class' => 'form-horizontal']);
        $form->setTagID($form->getTagName());
        if (!is_null($service->getMyKey())) {
            $form->addItem(new \Ease\Html\InputHiddenTag($service->getMyKeyColumn(),
                $service->getMyKey()));
        }
        $form->addItem('<br>');
        $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        $oPage->AddCss('
input.ui-button { width: 100%; }
');

        if ($service->getID()) {
            $tools->addItem($service->deleteButton());
            $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
                $service->transferForm()));
            $tools->addItem($service->cloneButton());


            $renameForm = new \Ease\TWB\Form('Rename',
                '?action=rename&service_id='.$service->getId());
            $renameForm->addItem(new \Ease\Html\InputTextTag('newname'),
                $service->getName(), ['class' => 'form-control']);
            $renameForm->addItem(new \Ease\TWB\SubmitButton(_('Přejmenovat'),
                'success'));

            $tools->addItem(new \Ease\TWB\Panel(_('Přejmenování'), 'info',
                $renameForm));
            $tools->addItem(new UI\HostSelector($service));

            if ($oUser->getSettingValue('admin')) {
                $tools->addItem(new \Ease\TWB\LinkButton('?action=system&service_id='.$service->getId(),
                    _('Systémová služba')));
            }

            $tools->addItem(new \Ease\TWB\Panel(_('Výměna služby'), 'info',
                new UI\ServiceSwapForm($service)));
        }

        $mainPanel->addItem(new \Ease\TWB\Panel(new \Ease\Html\H3Tag([new UI\PlatformIcon($service->getDataValue('platform')),
            $service->getName()]), 'default', $form));

        break;
}



$oPage->addItem(new UI\PageBottom());
$oPage->draw();
