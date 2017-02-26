<?php

namespace Icinga\Editor;

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

$service = new Engine\Service($oPage->getRequestValue('service_id', 'int'));
$host    = new Engine\Host($oPage->getRequestValue('host_id', 'int'));

switch ($oPage->getRequestValue('action')) {

    case 'clone':
        $service->setDataValue('parent_id', $service->getId());
        $service->unsetDataValue($service->getmyKeyColumn());
        $service->addMember(
            'host_name', $host->getId(), $host->getName()
        );

        $service->setDataValue('hostgroup_name', []);
        $service->setDataValue('user_id', $oUser->getID());
        $service->setDataValue($service->nameColumn,
            _('Klon').' '.$service->getName());
        if ($service->saveToSQL()) {
            $oUser->addStatusMessage(_('Služba byla naklonována'), 'success');
            $oPage->redirect('servicetweak.php?service_id='.$service->getId().'&host_id='.$host->getId());
        } else {
            $oUser->addStatusMessage(_('Sužba nebyla naklonována'), 'warning');
        }
        break;
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($service->rename($newname)) {
                $oUser->addStatusMessage(_('Služba byla přejmenována'),
                    'success');
            } else {
                $oUser->addStatusMessage(_('Sužba nebyla přejmenována'),
                    'warning');
            }
        }
        break;
    default :
//        $service->addStatusMessage(_('Případné změny budou uloženy do odvozené služby'));
        break;
}

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $service->delete();
    $oPage->redirect('host.php?host_id='.$host->getId());
    exit();
}

if ($service->getOwnerID() != $oUser->getMyKey()) {
    if ($service->fork($host)) {
        $oUser->addStatusMessage(_('Služba jiného vlastníka byla odvozena jako vlastní'),
            'success');
    } else {
        $oUser->addStatusMessage(_('Služba nebyla odvozena'), 'error');
    }
}

$delhost = $oPage->getGetValue('delhost');
if ($delhost) {
    $service->delMember(
        'host_name', $oPage->getGetValue('host_id', 'int'), $delhost
    );
    $service->saveToSQL();
}

$addhost = $oPage->getGetValue('addhost');
if ($addhost) {
    $service->addMember(
        'host_name', $oPage->getGetValue('host_id', 'int'), $addhost
    );
    $service->saveToSQL();
}

$delcnt = $oPage->getGetValue('delcontact');
if ($delcnt) {
    $service->delMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $delcnt
    );
    $service->saveToSQL();
}

$addcnt = $oPage->getGetValue('addcontact');
if ($addcnt) {
    $service->addMember(
        'contacts', $oPage->getGetValue('contact_id', 'int'), $addcnt
    );
    $service->saveToSQL();
}

$oPage->addItem(new UI\PageTop(_('Editace služby').' '.$service->getName()));
$oPage->addPageColumns();

$serviceTweak = new UI\ServiceTweaker($service, $host);

$serviceName = $service->getDataValue('display_name');
if (!$serviceName) {
    $serviceName = $service->getName();
}
$oPage->columnII->addItem(new \Ease\Html\H3Tag([new UI\PlatformIcon($service->getDataValue('platform')),
    $serviceName]));

$oPage->columnII->addItem($serviceTweak);

$oPage->columnIII->addItem($service->deleteButton($service->getName(),
        'host_id='.$host->getId()));

$oPage->columnIII->addItem(new \Ease\TWB\LinkButton('service.php?service_id='.$service->getID(),
    _('Service Edit').' '.$serviceName));

$renameForm = new \Ease\TWB\Form('Rename',
    '?action=rename&amp;host_id='.$host->getID().'&service_id='.$service->getId());
$renameForm->addItem(new \Ease\Html\InputTextTag('newname'),
    $service->getName(), ['class' => 'form-control']);
$renameForm->addItem(new \Ease\TWB\SubmitButton(_('Rename'), 'success'));

$oPage->columnIII->addItem(new \Ease\TWB\Panel(_('Renaming'), 'info',
    $renameForm));

$oPage->columnIII->addItem($service->cloneButton());


$oPage->columnI->addItem(new UI\HostSelector($service));
$oPage->columnI->addItem(new UI\ContactSelector($service));

$oPage->columnIII->addItem(new \Ease\TWB\LinkButton('host.php?host_id='.$host->getId(),
    [_('Zpět na').' ', $host, ' ', $host->getName()], 'default'));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
